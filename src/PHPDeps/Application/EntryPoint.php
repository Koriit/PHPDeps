<?php

namespace Koriit\PHPDeps\Application;

use DI\Container;
use DI\ContainerBuilder;
use Exception;
use Psr\Container\ContainerInterface;
use RuntimeException;

class EntryPoint
{
    /**
     * @param string   $appClass
     * @param callable $dependenciesDefFactory
     */
    public function enter($appClass, callable $dependenciesDefFactory)
    {
        $container = $this->createContainer($dependenciesDefFactory());

        try {
            $application = $this->createApplication($container, $appClass);
        } catch (Exception $e) {
            throw new RuntimeException('Could not create application: ' . $appClass, 0, $e);
        }

        $application->run();
    }

    /**
     * @param array $definitionSources
     *
     * @return Container
     */
    protected function createContainer(array $definitionSources)
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAnnotations(false);
        $containerBuilder->useAutowiring(true);
        $containerBuilder->addDefinitions($definitionSources);

        $container = $containerBuilder->build();
        $container->set(ContainerInterface::class, $container);

        return $container;
    }

    /**
     * @param Container $container
     * @param string    $appClass
     *
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     *
     * @return ApplicationInterface
     */
    protected function createApplication(Container $container, $appClass)
    {
        $application = $container->get($appClass);
        $container->set(ApplicationInterface::class, $application);

        return $application;
    }
}
