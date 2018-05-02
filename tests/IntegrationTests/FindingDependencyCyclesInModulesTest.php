<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPDeps\Test\IntegrationTests;

use Koriit\PHPDeps\Config\ConfigReader;
use Koriit\PHPDeps\Config\ConfigValidator;
use Koriit\PHPDeps\Modules\Module;
use Koriit\PHPDeps\Modules\ModuleReader;
use Koriit\PHPDeps\Tokenizer\DependenciesReader;
use Koriit\PHPDeps\Tokenizer\Exceptions\MalformedFile;
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
     * @throws \Koriit\PHPDeps\Config\Exceptions\InvalidConfig
     * @throws \Koriit\PHPDeps\Config\Exceptions\InvalidSchema
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
                    __DIR__ . '/../Cases/Integration/AcyclicModules/phpdeps.xml',
                    [],
              ],

              'Three Cyclic Modules' => [
                    __DIR__ . '/../Cases/Integration/ThreeCyclicModules/phpdeps.xml',
                    [
                          ['Module1', 'Module2', 'Module3'],
                    ],
              ],

              'Two Disconnected Cycles' => [
                    __DIR__ . '/../Cases/Integration/TwoDisconnectedCycles/phpdeps.xml',
                    [
                          ['Module1', 'Module2'],
                          ['Module3', 'Module4'],
                    ],
              ],
        ];
    }
}
