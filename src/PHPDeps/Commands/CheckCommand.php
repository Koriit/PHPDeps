<?php

namespace Koriit\PHPDeps\Commands;

use Koriit\PHPDeps\Config\Exceptions\InvalidConfig;
use Koriit\PHPDeps\Config\Exceptions\InvalidSchema;
use Koriit\PHPDeps\Console\GraphWriter;
use Koriit\PHPDeps\ExitCodes;
use Koriit\PHPDeps\Helpers\InputHelper;
use Koriit\PHPDeps\Helpers\ModulesHelper;
use Koriit\PHPDeps\Modules\Module;
use Koriit\PHPDeps\Modules\ModuleReader;
use Koriit\PHPDeps\Tokenizer\Exceptions\MalformedFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckCommand extends Command
{
    /** @var ModuleReader */
    private $modulesReader;

    /** @var GraphWriter */
    private $graphWriter;

    /** @var ModulesHelper */
    private $modulesHelper;

    /** @var InputHelper */
    private $inputHelper;

    public function __construct(ModulesHelper $modulesHelper, InputHelper $inputHelper, ModuleReader $modulesReader, GraphWriter $graphWriter)
    {
        parent::__construct();

        $this->modulesReader = $modulesReader;
        $this->graphWriter = $graphWriter;
        $this->modulesHelper = $modulesHelper;
        $this->inputHelper = $inputHelper;
    }

    protected function configure()
    {
        $this
              ->setName('check')
              ->setDescription('Check whether there are circular dependencies in modules.')
              ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Custom location of configuration file', './phpdeps.xml')
              ->addOption('graphs', 'g', InputOption::VALUE_NONE, 'Whether to display dependency cycles as graphs(assumed with -v)');
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
        $drawGraphs = $output->isVerbose() || $input->getOption('graphs');

        $modules = $this->modulesHelper->findModules($config);
        if (!$this->modulesHelper->validateModules($modules, $io)) {
            return ExitCodes::UNEXPECTED_ERROR;
        }

        $dependencyCycles = $this->findDependencyCycles($modules);

        if (empty($dependencyCycles)) {
            $io->success('There are no circular dependencies in your modules!');

            return ExitCodes::OK;
        } else {
            $io->warning('There are circular dependencies in your modules!');

            $this->displayDependencyCycles($dependencyCycles, $io, $drawGraphs);

            return ExitCodes::CIRCULAR_DEPENDENCIES_EXIST;
        }
    }

    /**
     * @param Module[][]   $dependencyCycles
     * @param SymfonyStyle $io
     * @param bool         $drawGraphs
     */
    private function displayDependencyCycles(array $dependencyCycles, SymfonyStyle $io, $drawGraphs)
    {
        $cyclesCount = \count($dependencyCycles);
        $io->writeln('In total there ' . ($cyclesCount > 1 ? 'are ' . $cyclesCount . ' dependency cycles' : 'is 1 dependency cycle') . ' in your modules.');

        $i = 1;
        foreach ($dependencyCycles as $cycle) {
            $graphNodes = [];
            $out = $i++ . '. ';

            foreach ($cycle as $module) {
                $out .= '<fg=white>' . $module->getName() . '</> -> ';
                $graphNodes[] = $module->getName() . ' [<fg=magenta>' . $module->getNamespace() . '</>]';
            }

            $io->section($out . '<fg=white>' . $cycle[0]->getName() . '</>');
            if ($drawGraphs) {
                $this->graphWriter->drawGraphCycle($graphNodes);
                if ($i <= $cyclesCount) {
                    $io->newLine();
                    $io->newLine();
                }
            }
        }
    }

    /**
     * @param Module[] $modules
     *
     * @throws MalformedFile
     *
     * @return Module[][]
     */
    private function findDependencyCycles($modules)
    {
        $dependenciesGraph = $this->modulesReader->generateDependenciesGraph($modules);

        return $dependenciesGraph->findAllCycles();
    }
}
