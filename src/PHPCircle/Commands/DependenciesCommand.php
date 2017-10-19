<?php

namespace Koriit\PHPCircle\Commands;

use Koriit\PHPCircle\Config\Config;
use Koriit\PHPCircle\Config\ConfigReader;
use Koriit\PHPCircle\Config\Exceptions\InvalidConfig;
use Koriit\PHPCircle\Config\Exceptions\InvalidSchema;
use Koriit\PHPCircle\ExitCodes;
use Koriit\PHPCircle\Modules\Module;
use Koriit\PHPCircle\Modules\ModuleReader;
use Koriit\PHPCircle\Tokenizer\Exceptions\MalformedFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function array_merge;
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
              ->setName("dependencies")
              ->setDescription("Lists modules and their dependencies.")
              ->addOption("config", 'c', InputOption::VALUE_OPTIONAL, "Custom location of configuration file", "./phpcircle.xml");
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws InvalidConfig
     * @throws InvalidSchema
     * @throws MalformedFile
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = $input->getOption("config");

        $io = new SymfonyStyle($input, $output);

        $config = $this->configReader->readConfig($configFile);

        $modules = $this->findModules($config);

        $duplicatedModules = $this->findModuleDuplicates($modules);
        if (!empty($duplicatedModules)) {
            $io->error("Two or more of your configured modules have the same name");
            $io->section("Duplicated modules");
            $io->listing($duplicatedModules);

            return ExitCodes::UNEXPECTED_ERROR;
        }

        $dependenciesGraph = $this->modulesReader->generateDependenciesGraph($modules);

        $i = 1;
        foreach ($dependenciesGraph->getVertices() as $vertex) {
            /** @var Module $module */
            $module = $vertex->getValue();
            $io->section($i++ . '. ' . $module->getName() . ' [<fg=magenta>' . $module->getNamespace() . '</>]');

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

        return ExitCodes::OK;
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
            if (in_array($name, $names)) {
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
            $modules = array_merge($modules, $detector->findModules());
        }

        return $modules;
    }
}
