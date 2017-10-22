<?php

namespace Koriit\PHPCircle\Commands;

use Koriit\PHPCircle\Config\Exceptions\InvalidConfig;
use Koriit\PHPCircle\Config\Exceptions\InvalidSchema;
use Koriit\PHPCircle\ExitCodes;
use Koriit\PHPCircle\Graph\DirectedGraph;
use Koriit\PHPCircle\Graph\Vertex;
use Koriit\PHPCircle\Helpers\CommandHelper;
use Koriit\PHPCircle\Modules\Module;
use Koriit\PHPCircle\Modules\ModuleReader;
use Koriit\PHPCircle\Tokenizer\Exceptions\MalformedFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DependenciesCommand extends Command
{
    /** @var ModuleReader */
    private $modulesReader;

    /** @var CommandHelper */
    private $helper;

    public function __construct(CommandHelper $helper, ModuleReader $modulesReader)
    {
        parent::__construct();

        $this->modulesReader = $modulesReader;
        $this->helper = $helper;
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

        $config = $this->helper->readConfig($input);
        $filters = $this->readFilters($input);

        $modules = $this->helper->findModules($config);
        if (!$this->helper->validateModules($modules, $io)) {
            return ExitCodes::UNEXPECTED_ERROR;
        }

        $dependenciesGraph = $this->modulesReader->generateDependenciesGraph($modules);
        $this->displayModules($dependenciesGraph, $io, $filters);

        return ExitCodes::OK;
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
                $io->writeln('Displaying modules with following filter: ' . \implode(', ', $filters));
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
     * @return string[] Array of filtered module names
     */
    private function readFilters(InputInterface $input)
    {
        $filters = $input->getOption('filter');
        if (empty(\trim($filters))) {
            return [];
        }

        $filters = \explode(',', $filters);
        $filters = \array_map('trim', $filters);
        \sort($filters);

        return $filters;
    }
}
