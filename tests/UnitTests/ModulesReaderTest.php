<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Test\UnitTests;


use function in_array;
use Koriit\PHPCircle\Graph\Vertex;
use Koriit\PHPCircle\Module;
use Koriit\PHPCircle\ModulesReader;
use Koriit\PHPCircle\Tokenizer\DependenciesReader;
use Koriit\PHPCircle\Tokenizer\Exceptions\MalformedFile;
use PHPUnit_Framework_TestCase;
use function print_r;

class ModulesReaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ModulesReader
     */
    private $reader;

    public function setUp()
    {
        $this->reader = new ModulesReader(new DependenciesReader());
    }

    /**
     * @test
     */
    public function shouldReadDirModuleDependencies()
    {
        $expected = [
              "Vendor\Library2\Module\AnotherClass",
              "Vendor\Library\Module1\SomeClass",
              "Vendor\Library\Module2\Package\SomeOtherClass",
              "Vendor\Library\Module3\Package\SomeYetAnotherClass",
              "Vendor\Library2\Module\SomeClass",
        ];

        $module = __DIR__ . "/../Cases/Modules/DirModule";

        $dependencies = $this->reader->findModuleDependencies($module);

        $this->assertEquals($expected, $dependencies, '', 0.0, 10, true);
    }

    /**
     * @test
     */
    public function shouldReadFileModuleDependencies()
    {
        $expected = [
              "Vendor\Library\Module1\SomeClass",
              "Vendor\Library\Module2\Package\SomeOtherClass",
        ];

        $module = __DIR__ . "/../Cases/Modules/DirModule/Class.php";

        $dependencies = $this->reader->findModuleDependencies($module);

        $this->assertEquals($expected, $dependencies, '', 0.0, 10, true);
    }

    /**
     * @test
     *
     * @dataProvider getGraphCases
     *
     * @param string $case
     * @param array  $expectations
     *
     * @throws MalformedFile
     */
    public function shouldGenerateModuleDependenciesGraph($case, array $expectations)
    {
        $modules = [];
        $modulesCount = count($expectations);
        for ($i = 1; $i <= $modulesCount; $i++) {
            $modules[] = new Module('Module' . $i, 'Vendor\Library\Module' . $i, __DIR__ . '/../Cases/Modules/' . $case . '/Module' . $i);
        }

        $graph = $this->reader->generateDependenciesGraph($modules);

        foreach ($graph->getVertices() as $vertex) {
            $dependencies = [];
            foreach ($vertex->getNeighbours() as $neighbour) {
                $dependencies[] = $neighbour->getValue()->getName();
            }

            $this->assertEquals($expectations[$vertex->getValue()->getName()], $dependencies, '', 0.0, 10, true);
        }
    }

    public function getGraphCases()
    {
        return [
              "Acyclic Modules" => [
                    'AcyclicModules',
                    [
                          "Module1" => [],
                          "Module2" => [],
                    ],
              ],

              "Two Cyclic Modules" => [
                    'TwoCyclicModules',
                    [
                          "Module1" => ["Module2"],
                          "Module2" => ["Module1"],
                    ],
              ],

              "Three Cyclic Modules" => [
                    'ThreeCyclicModules',
                    [
                          "Module1" => ["Module2"],
                          "Module2" => ["Module3"],
                          "Module3" => ["Module1"],
                    ],
              ],

              "Two Connected Cycles" => [
                    'TwoConnectedCycles',
                    [
                          "Module1" => ["Module2"],
                          "Module2" => ["Module1", "Module3"],
                          "Module3" => ["Module2"],
                    ],
              ],

              "Two Disconnected Cycles" => [
                    'TwoDisconnectedCycles',
                    [
                          "Module1" => ["Module2"],
                          "Module2" => ["Module1"],
                          "Module3" => ["Module4"],
                          "Module4" => ["Module3"],
                    ],
              ],
        ];
    }
}
