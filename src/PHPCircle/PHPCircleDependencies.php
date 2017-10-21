<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use function DI\object;

class PHPCircleDependencies
{
    public function __invoke()
    {
        return [
              InputInterface::class => object(ArgvInput::class),

              OutputInterface::class => object(ConsoleOutput::class),
        ];
    }
}
