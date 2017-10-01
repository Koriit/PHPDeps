<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle;


use Koriit\EventDispatcher\EventDispatcherInterface;
use Koriit\PHPCircle\Application\ApplicationInterface;
use Koriit\PHPCircle\Application\Exceptions\ApplicationAlreadyRunning;
use Koriit\PHPCircle\Events\AppExecutedCommandEvent;
use Koriit\PHPCircle\Events\AppFinalizedEvent;
use Koriit\PHPCircle\Events\AppInitializedEvent;
use Koriit\PHPCircle\Events\AppLoadedCommandsEvent;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PHPCircleApplication implements ApplicationInterface
{
    /** @var boolean */
    private $running;

    /** @var ContainerInterface */
    private $container;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var Application */
    private $consoleKernel;

    public function __construct(ContainerInterface $container, EventDispatcherInterface $eventDispatcher, Application $consoleKernel)
    {
        $this->running = false;
        $this->container = $container;
        $this->eventDispatcher = $eventDispatcher;
        $this->consoleKernel = $consoleKernel;
    }

    public function run()
    {
        if ($this->running) {
            throw new ApplicationAlreadyRunning();
        }

        $this->running = true;

        $this->initialize();
        $this->loadCommands();
        $exitCode = $this->executeCommand();
        $this->finalize($exitCode);

        $this->running = false;

        exit($exitCode > ExitCodes::STATUS_OUT_OF_RANGE ? ExitCodes::STATUS_OUT_OF_RANGE : $exitCode);
    }

    public function isRunning()
    {
        return $this->running;
    }

    public function getName()
    {
        return "PHPCircle";
    }

    protected function initialize()
    {
        $this->consoleKernel->setName("<info>" . $this->getName() . "</info>, a tool for finding circular dependencies in your modules.");
        $this->consoleKernel->setAutoExit(false);

        $this->eventDispatcher->addListeners(include 'phpcircle_listeners.php');

        $event = new AppInitializedEvent();
        $this->eventDispatcher->dispatch(AppInitializedEvent::class, ["event" => $event]);
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function loadCommands()
    {
        $event = new AppLoadedCommandsEvent();
        $this->eventDispatcher->dispatch(AppLoadedCommandsEvent::class, ["event" => $event]);
    }

    /**
     * @return int Exit code
     *
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function executeCommand()
    {
        $exitCode = $this->consoleKernel->run(
              $this->container->get(InputInterface::class),
              $this->container->get(OutputInterface::class)
        );

        $event = new AppExecutedCommandEvent($exitCode);
        $this->eventDispatcher->dispatch(AppExecutedCommandEvent::class, ["event" => $event, "exitCode" => $exitCode]);

        return $exitCode;
    }

    protected function finalize($exitCode)
    {
        $event = new AppFinalizedEvent($exitCode);
        $this->eventDispatcher->dispatch(AppFinalizedEvent::class, ["event" => $event, "exitCode" => $exitCode]);
    }
}