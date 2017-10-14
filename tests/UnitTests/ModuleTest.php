<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Test\UnitTests;

use Koriit\PHPCircle\Module;
use PHPUnit_Framework_TestCase;

class ModuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldReturnProperPatternForFile()
    {
        $module = new Module("Test", 'Some\Namespace\Module\Class', __FILE__);

        $pattern = $module->getPattern();

        $this->assertEquals('/^Some\\\\Namespace\\\\Module\\\\Class$/', $pattern);
    }

    /**
     * @test
     */
    public function shouldReturnProperPatternForDir()
    {
        $module = new Module("Test", 'Some\Namespace\Module', __DIR__);

        $pattern = $module->getPattern();

        $this->assertEquals('/^Some\\\\Namespace\\\\Module(\\\\.+)?$/', $pattern);
    }
}
