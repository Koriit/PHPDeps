<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Test\UnitTests;

use Koriit\PHPCircle\Config\Config;
use Koriit\PHPCircle\Config\ConfigReader;
use Koriit\PHPCircle\Config\ConfigValidator;
use Koriit\PHPCircle\Modules\ModuleDetector;
use Koriit\PHPCircle\Config\Exceptions\InvalidConfig;
use Koriit\PHPCircle\Config\Exceptions\InvalidSchema;
use Koriit\PHPCircle\Modules\Module;
use PHPUnit_Framework_TestCase;
use const DIRECTORY_SEPARATOR;

class ConfigReaderTest extends PHPUnit_Framework_TestCase
{
    /** @var ConfigReader */
    private $reader;

    public function setUp()
    {
        $this->reader = new ConfigReader(new ConfigValidator());
    }

    /**
     * @test
     * @throws InvalidSchema
     * @throws InvalidConfig
     */
    public function shouldReturnConfig()
    {
        $config = $this->reader->readConfig(__DIR__ . '/../Cases/Configs/OneModule.xml');

        $this->assertInstanceOf(Config::class, $config);
    }

    /**
     * @test
     * @throws InvalidSchema
     * @throws InvalidConfig
     */
    public function shouldThrowWhenInvalidSchema()
    {
        $this->setExpectedException(InvalidSchema::class);

        $this->reader->readConfig(__DIR__ . '/../Cases/Configs/InvalidSchema.xml');
    }

    /**
     * @test
     * @throws InvalidSchema
     * @throws InvalidConfig
     */
    public function shouldProperlyReadComplexConfig()
    {
        $modules = [
              new Module('Module1', 'Vendor\Library\Module1', realpath(__DIR__ . '/../Cases/Configs') . DIRECTORY_SEPARATOR . './src/Library/Module1'),
              new Module('Module2', 'Vendor\Library\Module2', realpath(__DIR__ . '/../Cases/Configs') . DIRECTORY_SEPARATOR . './src/Library/Module2'),
        ];

        $moduleDetectors = [
              new ModuleDetector('Vendor\Library1', realpath(__DIR__ . '/../Cases/Configs') . DIRECTORY_SEPARATOR . './src/Library1'),
              new ModuleDetector('Vendor\Library2', realpath(__DIR__ . '/../Cases/Configs') . DIRECTORY_SEPARATOR . './src/Library2'),
        ];
        $expectedConfig = new Config($modules, $moduleDetectors);

        $config = $this->reader->readConfig(__DIR__ . '/../Cases/Configs/Complex.xml');

        $this->assertEquals($expectedConfig, $config);
    }
}
