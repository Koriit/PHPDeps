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

class ModulesCommand extends Command
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
              ->setName('modules')
              ->setDescription('Lists configured modules')
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

        $modules = $this->helper->findModules($config);
        if (!$this->helper->validateModules($modules, $io)) {
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
