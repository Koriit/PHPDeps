<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle;


use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

class CommandsLoader
{
    /**
     * @param Application        $kernel
     * @param ContainerInterface $container
     *
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(Application $kernel, ContainerInterface $container)
    {
        foreach ($this->getCommandsList() as $command) {
            $kernel->add($container->get($command));
        }
    }

    /**
     * @return string[]
     */
    public function getCommandsList()
    {
        return [
        ];
    }
}