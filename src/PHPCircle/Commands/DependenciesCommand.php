<?php

namespace Koriit\PHPCircle\Commands;

use Koriit\PHPCircle\Config\Exceptions\InvalidConfig;
use Koriit\PHPCircle\Config\Exceptions\InvalidSchema;
use Koriit\PHPCircle\ExitCodes;
use Koriit\PHPCircle\Graph\DirectedGraph;
use Koriit\PHPCircle\Helpers\InputHelper;
use Koriit\PHPCircle\Helpers\ModulesHelper;
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

        $config = $this->inputHelper->readConfig($input);
        $filters = $this->inputHelper->readFilters($input);

        $modules = $this->modulesHelper->findModules($config);
        if (!$this->modulesHelper->validateModules($modules, $io)) {
            return ExitCodes::UNEXPECTED_ERROR;
        }

        $dependenciesGraph = $this->modulesReader->generateDependenciesGraph($modules);
        $this->displayModules($dependenciesGraph, $io, $filters);

        return ExitCodes::OK;
    }

    /**
     * @param DirectedGraph $dependenciesGraph
     * @param SymfonyStyle  $io
     * @param string[]      $filters           Filtered module names
     */
    private function displayModules(DirectedGraph $dependenciesGraph, SymfonyStyle $io, array $filters)
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

        $i = 1;
        foreach ($vertices as $vertex) {
            $this->modulesHelper->renderModuleDependencies($io, $vertex, $i++);
        }
    }
}
