<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Test\IntegrationTests;

use Koriit\PHPCircle\Config\ConfigReader;
use Koriit\PHPCircle\Config\ConfigValidator;
use Koriit\PHPCircle\Modules\Module;
use Koriit\PHPCircle\Modules\ModuleReader;
use Koriit\PHPCircle\Tokenizer\DependenciesReader;
use Koriit\PHPCircle\Tokenizer\Exceptions\MalformedFile;
use PHPUnit_Framework_TestCase;

class FindingDependencyCyclesInModulesTest extends PHPUnit_Framework_TestCase
{
    /** @var ConfigReader */
    private $configReader;

    /** @var ModuleReader */
    private $moduleReader;

    public function setUp()
    {
        $this->moduleReader = new ModuleReader(new DependenciesReader());
        $this->configReader = new ConfigReader(new ConfigValidator());
    }

    /**
     * @test
     *
     * @dataProvider getGraphCases
     *
     * @param string $configFile
     * @param array  $expectedCycles
     *
     * @throws MalformedFile
     * @throws \Koriit\PHPCircle\Config\Exceptions\InvalidConfig
     * @throws \Koriit\PHPCircle\Config\Exceptions\InvalidSchema
     */
    public function shouldFindDependencyCycles($configFile, array $expectedCycles)
    {
        $config = $this->configReader->readConfig($configFile);

        $graph = $this->moduleReader->generateDependenciesGraph($config->getModules());
        /** @var Module[][] $moduleCycles */
        $moduleCycles = $graph->findAllCycles();

        $cycles = [];
        foreach ($moduleCycles as $moduleCycle) {
            $cycle = [];
            foreach ($moduleCycle as $module) {
                $cycle[] = $module->getName();
            }

            $cycles[] = $cycle;
        }

        $this->assertEquals($expectedCycles, $cycles);
    }

    public function getGraphCases()
    {
        return [
              'Acyclic Modules' => [
                    __DIR__ . '/../Cases/Integration/AcyclicModules/phpcircle.xml',
                    [],
              ],

              'Three Cyclic Modules' => [
                    __DIR__ . '/../Cases/Integration/ThreeCyclicModules/phpcircle.xml',
                    [
                          ['Module1', 'Module2', 'Module3'],
                    ],
              ],

              'Two Disconnected Cycles' => [
                    __DIR__ . '/../Cases/Integration/TwoDisconnectedCycles/phpcircle.xml',
                    [
                          ['Module1', 'Module2'],
                          ['Module3', 'Module4'],
                    ],
              ],
        ];
    }
}
