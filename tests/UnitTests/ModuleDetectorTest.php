<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPDeps\Test\UnitTests;

use Koriit\PHPDeps\Modules\Module;
use Koriit\PHPDeps\Modules\ModuleDetector;
use PHPUnit_Framework_TestCase;

class ModuleDetectorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     *
     * @dataProvider getDetectionCases
     *
     * @param ModuleDetector $detector
     * @param array          $expectedModules
     */
    public function shouldFindModules(ModuleDetector $detector, array $expectedModules)
    {
        $modules = $detector->findModules();

        $this->assertCount(\count($expectedModules), $modules);

        foreach ($modules as $module) {
            $this->assertEquals($expectedModules[$module->getName()], $module);
        }
    }

    public function getDetectionCases()
    {
        $casesDir = \realpath(__DIR__ . '/../Cases/Detectors');

        return [
              'Simple' => [
                    'detector'        => new ModuleDetector("Some\Namespace", $casesDir . '/DirModules'),
                    'expectedModules' => [
                          'Module1' => new Module('Module1', "Some\Namespace\Module1", $casesDir . '/DirModules/Module1'),
                          'Module2' => new Module('Module2', "Some\Namespace\Module2", $casesDir . '/DirModules/Module2'),
                          'Module3' => new Module('Module3', "Some\Namespace\Module3", $casesDir . '/DirModules/Module3'),
                    ],
              ],

              'Module And File' => [
                    'detector'        => new ModuleDetector("Some\Namespace", $casesDir . '/ModuleAndFile'),
                    'expectedModules' => [
                          'Module' => new Module('Module', "Some\Namespace\Module", $casesDir . '/ModuleAndFile/Module'),
                    ],
              ],

              'Module With Subpackage' => [
                    'detector'        => new ModuleDetector("Some\Namespace", $casesDir . '/ModuleWithSubpackage'),
                    'expectedModules' => [
                          'Module' => new Module('Module', "Some\Namespace\Module", $casesDir . '/ModuleWithSubpackage/Module'),
                    ],
              ],

              'No Dir Modules' => [
                    'detector'        => new ModuleDetector("Some\Namespace", $casesDir . '/NoDirModules'),
                    'expectedModules' => [],
              ],
        ];
    }
}
