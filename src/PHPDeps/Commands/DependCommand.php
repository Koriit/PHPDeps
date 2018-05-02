<?php

namespace Koriit\PHPDeps\Commands;

use Koriit\PHPDeps\Config\Exceptions\InvalidConfig;
use Koriit\PHPDeps\Config\Exceptions\InvalidSchema;
use Koriit\PHPDeps\ExitCodes;
use Koriit\PHPDeps\Graph\DirectedGraph;
use Koriit\PHPDeps\Graph\Vertex;
use Koriit\PHPDeps\Helpers\InputHelper;
use Koriit\PHPDeps\Helpers\ModulesHelper;
use Koriit\PHPDeps\Modules\Module;
use Koriit\PHPDeps\Modules\ModuleReader;
use Koriit\PHPDeps\Tokenizer\Exceptions\MalformedFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DependCommand extends Command
{
    /** @var ModuleReader */
    private $modulesReader;

    /** @var ModulesHelper */
    private $modulesHelper;

    /** @var InputHelper */
    private $inputHelper;

    public function __construct(ModulesHelper $modulesHelper, InputHelper $inputHelper, ModuleReader $modulesReader)
    {
        parent::__construct();

        $this->modulesReader = $modulesReader;
        $this->modulesHelper = $modulesHelper;
        $this->inputHelper = $inputHelper;
    }

    protected function configure()
    {
        $this
              ->setName('depend')
              ->setDescription('Lists modules which depend on provided module')
              ->addArgument('module', InputArgument::REQUIRED, 'Name of module to search for')
              ->addOption('filter', 'f', InputOption::VALUE_OPTIONAL, 'Comma separated list of module names to show', '')
              ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Custom location of configuration file', './phpdeps.xml');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws InvalidConfig
     * @throws InvalidSchema
     * @throws MalformedFile
     *
     * @return int Exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $config = $this->inputHelper->readConfig($input);
        $filters = $this->inputHelper->readFilters($input);
        $moduleName = $input->getArgument('module');

        $modules = $this->modulesHelper->findModules($config);
        if (!$this->modulesHelper->validateModules($modules, $io) || !$this->checkModule($modules, $moduleName, $io)) {
            return ExitCodes::UNEXPECTED_ERROR;
        }

        $dependenciesGraph = $this->modulesReader->generateDependenciesGraph($modules);
        $this->displayDependantModules($dependenciesGraph, $io, $filters, $moduleName);

        return ExitCodes::OK;
    }

    /**
     * @param DirectedGraph $dependenciesGraph
     * @param SymfonyStyle  $io
     * @param string[]      $filters           Filtered module names
     * @param string        $moduleName
     */
    private function displayDependantModules(DirectedGraph $dependenciesGraph, SymfonyStyle $io, array $filters, $moduleName)
    {
        if ($io->isVerbose()) {
            if (empty($filters)) {
                $io->writeln('No filters applied.');
            } else {
                $io->writeln('Filters applied: ' . \implode(', ', $filters));
            }
            $io->newLine();
        }

        $vertices = $this->modulesHelper->filterVerticesByModuleName($dependenciesGraph->getVertices(), $filters);
        $vertices = $this->filterVerticesByDependencyName($vertices, $moduleName);

        $io->writeln(\count($vertices) . ' modules depend on "' . $moduleName . '"');

        $i = 1;
        foreach ($vertices as $vertex) {
            $this->modulesHelper->renderModuleDependencies($io, $vertex, $i++);
        }
    }

    /**
     * @param Module[]     $modules
     * @param string       $moduleName
     * @param SymfonyStyle $io
     *
     * @return bool Whether a module with provided name exists in the array
     */
    private function checkModule(array $modules, $moduleName, SymfonyStyle $io)
    {
        foreach ($modules as $module) {
            if ($module->getName() == $moduleName) {
                return true;
            }
        }

        $io->error('Module "' . $moduleName . '" is not a properly configured module');

        return false;
    }

    /**
     * @param Vertex[] $vertices       Module vertices to filter
     * @param string   $dependencyName Name of required dependency
     *
     * @return Vertex[] Filtered vertices array
     */
    private function filterVerticesByDependencyName(array $vertices, $dependencyName)
    {
        $filterExpression = function (Vertex $v) use ($dependencyName) {
            foreach ($v->getNeighbours() as $neighbour) {
                if ($neighbour->getValue()->getName() == $dependencyName) {
                    return true;
                }
            }

            return false;
        };

        return \array_filter($vertices, $filterExpression);
    }
}
