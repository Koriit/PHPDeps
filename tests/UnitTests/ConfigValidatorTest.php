<?php
/**
 * @copyright 2018 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPDeps\Test\UnitTests;

use Koriit\PHPDeps\Config\Config;
use Koriit\PHPDeps\Config\ConfigReader;
use Koriit\PHPDeps\Config\ConfigValidator;
use Koriit\PHPDeps\Config\Exceptions\InvalidConfig;
use Koriit\PHPDeps\Config\Exceptions\InvalidSchema;
use PHPUnit_Framework_TestCase;

class ConfigValidatorTest extends PHPUnit_Framework_TestCase
{
    /** @var ConfigValidator */
    private $validator;

    public function setUp()
    {
        $this->validator = new ConfigValidator();
    }

    /**
     * @test
     *
     * @throws InvalidConfig
     */
    public function shouldThrowWhenEmptyConfig()
    {
        $this->setExpectedException(InvalidConfig::class);

        $this->validator->check(new Config([], []));
    }

    /**
     * @test
     *
     * @throws InvalidConfig
     * @throws InvalidSchema
     */
    public function shouldThrowWhenDuplicatedModules()
    {
        $this->setExpectedException(InvalidConfig::class);

        $configReader = new ConfigReader($this->validator);
        $configReader->readConfig(__DIR__ . '/../Cases/Configs/DuplicatedModules.xml');
    }
}
