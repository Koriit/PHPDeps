<?php

namespace Koriit\PHPDeps\Commands;

use Koriit\PHPDeps\Config\Exceptions\InvalidConfig;
use Koriit\PHPDeps\Config\Exceptions\InvalidSchema;
use Koriit\PHPDeps\ExitCodes;
use Koriit\PHPDeps\Helpers\InputHelper;
use Koriit\PHPDeps\Helpers\ModulesHelper;
use Koriit\PHPDeps\Modules\Module;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ModulesCommand extends Command
{
    /** @var ModulesHelper */
    private $modulesHelper;

    /** @var InputHelper */
    private $inputHelper;

    public function __construct(ModulesHelper $modulesHelper, InputHelper $inputHelper)
    {
        parent::__construct();

        $this->modulesHelper = $modulesHelper;
        $this->inputHelper = $inputHelper;
    }

    protected function configure()
    {
        $this
              ->setName('modules')
              ->setDescription('Lists configured modules')
              ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Custom location of configuration file', './phpdeps.xml');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws InvalidConfig
     * @throws InvalidSchema
     *
     * @return int Exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $config = $this->inputHelper->readConfig($input);

        $modules = $this->modulesHelper->findModules($config);
        if (!$this->modulesHelper->validateModules($modules, $io)) {
            return ExitCodes::UNEXPECTED_ERROR;
        }

        if (empty($modules)) {
            $io->warning('There are no configured modules!');
        } else {
            $this->displayModules($modules, $io);
        }

        return ExitCodes::OK;
    }

    /**
     * @param Module[]     $modules
     * @param SymfonyStyle $io
     */
    private function displayModules(array $modules, SymfonyStyle $io)
    {
        $i = 1;
        foreach ($modules as $module) {
            $io->section($i++ . '. ' . $module->getName() . ' [<fg=magenta>' . $module->getNamespace() . '</>]');
        }
    }
}
