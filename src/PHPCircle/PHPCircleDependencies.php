<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle;


use DI\Scope;
use Koriit\EventDispatcher\EventDispatcher;
use Koriit\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class PHPCircleDependencies
{
    function __invoke()
    {
        return [
              EventDispatcherInterface::class => \DI\object(EventDispatcher::class)->scope(Scope::PROTOTYPE),

              InputInterface::class => \DI\object(ArgvInput::class),

              OutputInterface::class => \DI\object(ConsoleOutput::class),
        ];
    }
}