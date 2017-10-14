<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Test\UnitTests;

use Koriit\PHPCircle\Config\Config;
use Koriit\PHPCircle\Config\ConfigReader;
use Koriit\PHPCircle\Config\ConfigValidator;
use Koriit\PHPCircle\Config\Exceptions\InvalidConfig;
use Koriit\PHPCircle\Config\Exceptions\InvalidSchema;
use PHPUnit_Framework_TestCase;

class ConfigValidatorTest extends PHPUnit_Framework_TestCase
{
    /** @var ConfigReader */
    private $configReader;

    /** @var ConfigValidator */
    private $validator;

    public function setUp()
    {
        $this->validator = new ConfigValidator();
        $this->configReader = new ConfigReader();
    }

    /**
     * @test
     * @throws InvalidConfig
     */
    public function shouldThrowWhenEmptyConfig()
    {
        $this->setExpectedException(InvalidConfig::class);

        $this->validator->check(new Config([], []));
    }

    /**
     * @test
     * @throws InvalidConfig
     * @throws InvalidSchema
     */
    public function shouldThrowWhenDuplicatedModules()
    {
        $this->setExpectedException(InvalidConfig::class);

        $config = $this->configReader->readConfig(__DIR__ . '/../Cases/Configs/DuplicatedModules.xml');

        $this->validator->check($config);
    }
}
