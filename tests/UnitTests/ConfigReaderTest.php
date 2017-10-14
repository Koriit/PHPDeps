<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Test\UnitTests;

use const DIRECTORY_SEPARATOR;
use Koriit\PHPCircle\Config\Config;
use Koriit\PHPCircle\Config\ConfigReader;
use Koriit\PHPCircle\Config\DirDetector;
use Koriit\PHPCircle\Config\Exceptions\InvalidSchema;
use Koriit\PHPCircle\Module;
use PHPUnit_Framework_TestCase;

class ConfigReaderTest extends PHPUnit_Framework_TestCase
{
    /** @var ConfigReader */
    private $reader;

    public function setUp()
    {
        $this->reader = new ConfigReader();
    }

    /**
     * @test
     * @throws InvalidSchema
     */
    public function shouldReturnConfig()
    {
        $config = $this->reader->readConfig(__DIR__ . '/../Cases/Configs/OneModule.xml');

        $this->assertInstanceOf(Config::class, $config);
    }

    /**
     * @test
     * @throws InvalidSchema
     */
    public function shouldThrowWhenInvalidSchema()
    {
        $this->setExpectedException(InvalidSchema::class);

        $this->reader->readConfig(__DIR__ . '/../Cases/Configs/InvalidSchema.xml');
    }

    /**
     * @test
     * @throws InvalidSchema
     */
    public function shouldProperlyReadComplexConfig()
    {
        $modules = [
              new Module('Module1', 'Vendor\Library\Module1', realpath(__DIR__ . '/../Cases/Configs') . DIRECTORY_SEPARATOR . './src/Library/Module1'),
              new Module('Module2', 'Vendor\Library\Module2', realpath(__DIR__ . '/../Cases/Configs') . DIRECTORY_SEPARATOR . './src/Library/Module2'),
        ];

        $dirDetectors = [
              new DirDetector('Vendor\Library1', realpath(__DIR__ . '/../Cases/Configs') . DIRECTORY_SEPARATOR . './src/Library1'),
              new DirDetector('Vendor\Library2', realpath(__DIR__ . '/../Cases/Configs') . DIRECTORY_SEPARATOR . './src/Library2'),
        ];
        $expectedConfig = new Config($modules, $dirDetectors);

        $config = $this->reader->readConfig(__DIR__ . '/../Cases/Configs/Complex.xml');

        $this->assertEquals($expectedConfig, $config);
    }
}
