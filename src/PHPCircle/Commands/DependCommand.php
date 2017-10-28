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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DependCommand extends Command
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
              ->setName('depend')
              ->setDescription('Lists modules which depend on provided module')
              ->addArgument('module', InputArgument::REQUIRED, 'Name of module to search for')
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
        $filters = $this->helper->readFilters($input);
        $moduleName = $input->getArgument('module');

        $modules = $this->helper->findModules($config);
        if (!$this->helper->validateModules($modules, $io) || !$this->isModule($modules, $moduleName, $io)) {
            return ExitCodes::UNEXPECTED_ERROR;
        }

        $dependenciesGraph = $this->modulesReader->generateDependenciesGraph($modules);
        $this->displayDependantModules($dependenciesGraph, $io, $filters, $moduleName);

        return ExitCodes::OK;
    }


    /**
     * @param DirectedGraph $dependenciesGraph
     * @param SymfonyStyle  $io
     * @param string[]      $filters Filtered module names
     * @param string        $moduleName
     */
    private function displayDependantModules(DirectedGraph $dependenciesGraph, SymfonyStyle $io, array $filters, $moduleName)
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

        $vertices = \array_filter($vertices, function (Vertex $v) use ($moduleName) {
            foreach ($v->getNeighbours() as $neighbour) {
                if ($neighbour->getValue()->getName() == $moduleName) {
                    return true;
                }
            }

            return false;
        });

        if (!$io->isQuiet()) {
            $io->writeln(count($vertices) . ' modules depend on "' . $moduleName . '"');
        }

        $i = 1;
        foreach ($vertices as $vertex) {
            $this->helper->renderModuleDependencies($io, $vertex, $i++);
        }
    }

    /**
     * @param Module[]     $modules
     * @param string       $moduleName
     * @param SymfonyStyle $io
     *
     * @return bool Whether a module with provided name exists in the array
     */
    private function isModule(array $modules, $moduleName, SymfonyStyle $io)
    {
        foreach ($modules as $module) {
            if ($module->getName() == $moduleName) {
                return true;
            }
        }

        $io->error('Module "' . $moduleName . '" is not a properly configured module');

        return false;
    }
}
