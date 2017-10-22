<?php

namespace Koriit\PHPCircle\Commands;

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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
              ->setDescription('Lists module dependencies.')
              ->addArgument('module', InputArgument::OPTIONAL, 'Name of module to display. If omitted all modules are displayed')
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
        $configFile = $input->getOption('config');
        $moduleName = $input->getArgument('module');

        $io = new SymfonyStyle($input, $output);

        $config = $this->configReader->readConfig($configFile);

        $modules = $this->findModules($config);

        if (!$this->validateModules($modules, $io)) {
            return ExitCodes::UNEXPECTED_ERROR;
        }

        $dependenciesGraph = $this->modulesReader->generateDependenciesGraph($modules);

        if ($moduleName === null) {
            return $this->displayModules($dependenciesGraph, $io);
        } else {
            return $this->displayModule($dependenciesGraph, $moduleName, $io);
        }
    }

    /**
     * @param Module[] $modules
     *
     * @return string[]
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
    private function findModules($config)
    {
        $modules = $config->getModules();
        foreach ($config->getModuleDetectors() as $detector) {
            $modules = \array_merge($modules, $detector->findModules());
        }

        return $modules;
    }

    /**
     * @param DirectedGraph $dependenciesGraph
     * @param string        $moduleName Name of module to display
     * @param SymfonyStyle  $io
     *
     * @return int Exit code
     */
    private function displayModule(DirectedGraph $dependenciesGraph, $moduleName, SymfonyStyle $io)
    {
        $vertex = $dependenciesGraph->search(function (Vertex $v) use ($moduleName) {
            return $v->getValue()->getName() === $moduleName;
        });

        if ($vertex === null) {
            $io->error('Module "' . $moduleName . '" not found."');

            return ExitCodes::UNEXPECTED_ERROR;
        }

        $this->renderModule($io, $vertex);

        return ExitCodes::OK;
    }

    /**
     * @param DirectedGraph $dependenciesGraph
     * @param SymfonyStyle  $io
     *
     * @return int Exit code
     */
    private function displayModules(DirectedGraph $dependenciesGraph, SymfonyStyle $io)
    {
        $i = 1;
        foreach ($dependenciesGraph->getVertices() as $vertex) {
            $this->renderModule($io, $vertex, $i++);
        }

        return ExitCodes::OK;
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
     * @param int|null     $index  Whether module name should be prefixed with index number
     */
    private function renderModule(SymfonyStyle $io, Vertex $vertex, $index = null)
    {
        /** @var Module $module */
        $module = $vertex->getValue();
        $io->section(($index !== null ? $index . '. ' : '') . $module->getName() . ' [<fg=magenta>' . $module->getNamespace() . '</>]');

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
}
