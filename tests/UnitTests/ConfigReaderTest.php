<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Test\UnitTests;


use Koriit\PHPCircle\Config\ClassModule;
use Koriit\PHPCircle\Config\Config;
use Koriit\PHPCircle\Config\ConfigReader;
use Koriit\PHPCircle\Config\DirDetector;
use Koriit\PHPCircle\Config\DirModule;
use Koriit\PHPCircle\Config\Exceptions\InvalidSchema;
use Koriit\PHPCircle\Config\FileModule;
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
        $dirModules = [
              new DirModule('DirModule1', 'Vendor\Library\DirModule1', 'src/Library/DirModule1'),
              new DirModule('DirModule2', 'Vendor\Library\DirModule2', 'src/Library/DirModule2'),
        ];
        $classModules = [
              new ClassModule('ClassModule1', 'Vendor\Library\ClassModule1'),
              new ClassModule('ClassModule2', 'Vendor\Library\ClassModule2'),
        ];
        $fileModules = [
              new FileModule('FileModule1', 'Vendor\Library\FileModule1', 'src/Library/FileModule1.php'),
              new FileModule('FileModule2', 'Vendor\Library\FileModule2', 'src/Library/FileModule2.php'),
        ];
        $dirDetectors = [
              new DirDetector('Vendor\Library1', 'src/Library1'),
              new DirDetector('Vendor\Library2', 'src/Library2'),
        ];
        $expectedConfig = new Config($dirModules, $classModules, $fileModules, $dirDetectors);

        $config = $this->reader->readConfig(__DIR__ . '/../Cases/Configs/Complex.xml');

        $this->assertEquals($expectedConfig, $config);
    }
}
