<?php

namespace Koriit\PHPCircle\Commands;

use function implode;
use Koriit\PHPCircle\Config\Config;
use Koriit\PHPCircle\Config\ConfigReader;
use Koriit\PHPCircle\Config\Exceptions\InvalidConfig;
use Koriit\PHPCircle\Config\Exceptions\InvalidSchema;
use Koriit\PHPCircle\ExitCodes;
use Koriit\PHPCircle\Graph\DirectedGraph;
use Koriit\PHPCircle\Graph\Vertex;
use Koriit\PHPCircle\Modules\Module;
use Koriit\PHPCircle\Modules\ModuleReader;
use Koriit\PHPCircle\Tokenizer\Exceptions\MalformedFile;
use function print_r;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function array_filter;
use function array_walk;
use function explode;
use function in_array;

class DependenciesCommand extends Command
{
    /** @var ConfigReader */
    private $configReader;

    /** @var ModuleReader */
    private $modulesReader;

    public function __construct(ConfigReader $configReader, ModuleReader $modulesReader)
    {
        parent::__construct();

        $this->configReader = $configReader;
        $this->modulesReader = $modulesReader;
    }

    protected function configure()
    {
        $this
              ->setName('dependencies')
              ->setDescription('Lists module dependencies')
              ->addOption('filter', 'f', InputOption::VALUE_OPTIONAL, 'Comma separated list of module names to show', '')
              ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Custom location of configuration file', './phpcircle.xml');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws InvalidConfig
     * @throws InvalidSchema
     * @throws MalformedFile
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $config = $this->readConfig($input);
        $filters = $this->readFilters($input);

        $modules = $this->findModules($config);
        if (!$this->validateModules($modules, $io)) {
            return ExitCodes::UNEXPECTED_ERROR;
        }

        $dependenciesGraph = $this->modulesReader->generateDependenciesGraph($modules);
        $this->displayModules($dependenciesGraph, $io, $filters);

        return ExitCodes::OK;
    }

    /**
     * @param Module[] $modules
     *
     * @return string[] Names of duplicated modules
     */
    private function findModuleDuplicates(array $modules)
    {
        $moduleNames = [];
        foreach ($modules as $module) {
            $moduleNames[] = $module->getName();
        }

        $names = [];
        $duplicatedModules = [];
        foreach ($moduleNames as $name) {
            if (\in_array($name, $names)) {
                $duplicatedModules[] = $name;
            } else {
                $names[] = $name;
            }
        }

        return $duplicatedModules;
    }

    /**
     * @param Config $config
     *
     * @return Module[]
     */
    private function findModules(Config $config)
    {
        $modules = $config->getModules();
        foreach ($config->getModuleDetectors() as $detector) {
            $modules = \array_merge($modules, $detector->findModules());
        }

        return $modules;
    }

    /**
     * @param DirectedGraph $dependenciesGraph
     * @param SymfonyStyle  $io
     * @param string[]      $filters Filtered module names
     */
    private function displayModules(DirectedGraph $dependenciesGraph, SymfonyStyle $io, array $filters)
    {
        if ($io->isVerbose()) {
            if (empty($filters)) {
                $io->writeln('No filters applied.');
            } else {
                $io->writeln('Displaying modules with following filter: ' . implode(', ', $filters));
            }
            $io->newLine();
            $io->writeln('Modules:');
        }

        $vertices = $dependenciesGraph->getVertices();
        if (!empty($filters)) {
            $vertices = \array_filter($vertices, function (Vertex $v) use ($filters) {
                return \in_array($v->getValue()->getName(), $filters);
            });
        }

        $i = 1;
        foreach ($vertices as $vertex) {
            $this->renderModule($io, $vertex, $i++);
        }
    }

    /**
     * @param Module[]     $modules
     * @param SymfonyStyle $io
     *
     * @return bool True if everything is valid, false otherwise
     */
    private function validateModules(array $modules, SymfonyStyle $io)
    {
        $duplicatedModules = $this->findModuleDuplicates($modules);
        if (!empty($duplicatedModules)) {
            $io->error('Two or more of your configured modules have the same name');
            $io->section('Duplicated modules');
            $io->listing($duplicatedModules);

            return false;
        }

        return true;
    }

    /**
     * @param SymfonyStyle $io
     * @param Vertex       $vertex Module's vertex
     * @param int          $index  List index
     */
    private function renderModule(SymfonyStyle $io, Vertex $vertex, $index)
    {
        /** @var Module $module */
        $module = $vertex->getValue();
        $io->section($index . '. ' . $module->getName() . ' [<fg=magenta>' . $module->getNamespace() . '</>]');

        $dependencies = [];
        foreach ($vertex->getNeighbours() as $neighbour) {
            /** @var Module $dependency */
            $dependency = $neighbour->getValue();
            $dependencies[] = $dependency->getName() . ' [<fg=magenta>' . $dependency->getNamespace() . '</>]';
        }

        if (!empty($dependencies)) {
            $io->listing($dependencies);
        } else {
            $io->text('No dependencies');
        }
    }

    /**
     * @param InputInterface $input
     *
     * @throws InvalidConfig
     * @throws InvalidSchema
     *
     * @return Config
     */
    private function readConfig(InputInterface $input)
    {
        $configFile = $input->getOption('config');

        return $this->configReader->readConfig($configFile);
    }

    /**
     * @param InputInterface $input
     *
     * @return string[] Array of filtered module names
     */
    private function readFilters(InputInterface $input)
    {
        $filters = $input->getOption('filter');
        if (empty(\trim($filters))) {
            return [];
        }

        $filters = \explode(',', $filters);
        \array_walk($filters, 'trim');
        \sort($filters);

        return $filters;
    }
}
