<?php
/**
 * @copyright 2018 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPDeps;

use Exception;
use Koriit\PHPDeps\Application\ApplicationInterface;
use Koriit\PHPDeps\Application\Exceptions\ApplicationAlreadyRunning;
use Koriit\PHPDeps\Commands\CheckCommand;
use Koriit\PHPDeps\Commands\DependCommand;
use Koriit\PHPDeps\Commands\DependenciesCommand;
use Koriit\PHPDeps\Commands\ModulesCommand;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Application as ConsoleKernel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PHPDepsApplication implements ApplicationInterface
{
    const VERSION = "v0.1";

    /** @var bool */
    private $running;

    /** @var ContainerInterface */
    private $container;

    /** @var ConsoleKernel */
    private $consoleKernel;

    public function __construct(ContainerInterface $container, ConsoleKernel $consoleKernel)
    {
        $this->running = false;
        $this->container = $container;
        $this->consoleKernel = $consoleKernel;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function run()
    {
        if ($this->running) {
            throw new ApplicationAlreadyRunning();
        }

        $this->running = true;

        $this->initialize();
        $this->loadCommands();
        $exitCode = $this->executeCommand();

        $this->running = false;

        exit($exitCode > ExitCodes::STATUS_OUT_OF_RANGE ? ExitCodes::STATUS_OUT_OF_RANGE : $exitCode);
    }

    public function getName()
    {
        return 'PHPDeps';
    }

    public function isRunning()
    {
        return $this->running;
    }

    /**
     * @return string[]
     */
    public function getCommandsList()
    {
        return [
              CheckCommand::class,
              DependCommand::class,
              DependenciesCommand::class,
              ModulesCommand::class,
        ];
    }

    /**
     * @return string
     */
    public function getDefaultCommand()
    {
        return CheckCommand::class;
    }

    protected function initialize()
    {
        $this->consoleKernel->setName($this->getName());
        $this->consoleKernel->setVersion(self::VERSION);
        $this->consoleKernel->setAutoExit(false);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function loadCommands()
    {
        foreach ($this->getCommandsList() as $command) {
            $this->consoleKernel->add($this->container->get($command));
        }

        $this->consoleKernel->setDefaultCommand($this->container->get($this->getDefaultCommand())->getName());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     *
     * @return int Exit code
     */
    protected function executeCommand()
    {
        return $this->consoleKernel->run(
              $this->container->get(InputInterface::class),
              $this->container->get(OutputInterface::class)
        );
    }
}
