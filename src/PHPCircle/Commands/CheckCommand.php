<?php

namespace Koriit\PHPCircle\Commands;

use Koriit\PHPCircle\Config\Config;
use Koriit\PHPCircle\Config\ConfigReader;
use Koriit\PHPCircle\Config\Exceptions\InvalidConfig;
use Koriit\PHPCircle\Config\Exceptions\InvalidSchema;
use Koriit\PHPCircle\Console\GraphWriter;
use Koriit\PHPCircle\ExitCodes;
use Koriit\PHPCircle\Modules\Module;
use Koriit\PHPCircle\Modules\ModuleReader;
use Koriit\PHPCircle\Tokenizer\Exceptions\MalformedFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckCommand extends Command
{
    /** @var ConfigReader */
    private $configReader;

    /** @var ModuleReader */
    private $modulesReader;

    /** @var GraphWriter */
    private $graphWriter;

    public function __construct(ConfigReader $configReader, ModuleReader $modulesReader, GraphWriter $graphWriter)
    {
        parent::__construct();

        $this->configReader = $configReader;
        $this->modulesReader = $modulesReader;
        $this->graphWriter = $graphWriter;
    }

    protected function configure()
    {
        $this
              ->setName('check')
              ->setDescription('Check whether there are circular dependencies in modules.')
              ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Custom location of configuration file', './phpcircle.xml')
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
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $drawGraphs = $output->isVerbose() || $input->getOption('graphs');
        $configFile = $input->getOption('config');

        $io = new SymfonyStyle($input, $output);

        $config = $this->configReader->readConfig($configFile);

        $modules = $this->findModules($config);

        $duplicatedModules = $this->findModuleDuplicates($modules);
        if (!empty($duplicatedModules)) {
            $io->error('Two or more of your configured modules have the same name');
            $io->section('Duplicated modules');
            $io->listing($duplicatedModules);

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
        $io->writeln('In total there ' . ($cyclesCount > 1 ? 'are 2 dependency cycles' : 'is 1 dependency cycle') . ' in your modules.');

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
     * @return string[]
     */
    private function findModuleDuplicates($modules)
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
