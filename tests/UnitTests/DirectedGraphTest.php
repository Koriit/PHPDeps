<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Test\UnitTests;

use Koriit\PHPCircle\Graph\DirectedGraph;
use Koriit\PHPCircle\Graph\Vertex;
use PHPUnit_Framework_TestCase;
use stdClass;
use function array_keys;

class DirectedGraphTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldFindGraphCyclesWithComplexValuesAndComparators()
    {
        $obj1 = new stdClass();
        $obj1->value = 2;

        $obj2 = new stdClass();
        $obj2->value = 1;

        $vertex1 = new Vertex($obj1);
        $vertex2 = new Vertex($obj2);

        $vertex1->addNeighbour($vertex2);
        $vertex2->addNeighbour($vertex1);

        $vertices = [$vertex1, $vertex2];
        $graph = new DirectedGraph($vertices);

        // Default comparator
        $expectedCycles = [[$obj2, $obj1]];
        $cycles = $graph->findAllCycles();
        $this->assertEquals($expectedCycles, $cycles, print_r($cycles, true));

        // Custom comparator
        $expectedCycles = [[$obj1, $obj2]];
        $cycles = $graph->findAllCycles(function ($a, $b) {
            return $a->value > $b->value ? -1 : ($a->value < $b->value ? 1 : 0);
        });
        $this->assertEquals($expectedCycles, $cycles);
    }

    /**
     * @test
     *
     * @dataProvider getCycleCases
     *
     * @param string[][] $neighbourhoods
     * @param string[][] $expectedCycles
     */
    public function shouldFindGraphCycles(array $neighbourhoods, array $expectedCycles)
    {
        /** @var Vertex[] $vertices */
        $vertices = [];
        foreach (array_keys($neighbourhoods) as $module) {
            $vertices[$module] = new Vertex($module);
        }

        foreach ($neighbourhoods as $module => $neighbourhood) {
            foreach ($neighbourhood as $neighbour) {
                $vertices[$module]->addNeighbour($vertices[$neighbour]);
            }
        }

        $graph = new DirectedGraph($vertices);
        $cycles = $graph->findAllCycles();

        $this->assertEquals($expectedCycles, $cycles);
    }

    public function getCycleCases()
    {
        return [
              "No cycles" => [
                    "neighbourhoods" => [
                          "Module1" => ["Module2"],
                          "Module2" => [],
                    ],
                    "expectedCycles" => [],
              ],

              "2-node cycle" => [
                    "neighbourhoods" => [
                          "Module1" => ["Module2"],
                          "Module2" => ["Module1"],
                    ],
                    "expectedCycles" => [
                          ["Module1", "Module2"],
                    ],
              ],

              "3-node cycle" => [
                    "neighbourhoods" => [
                          "Module1" => ["Module2"],
                          "Module2" => ["Module3"],
                          "Module3" => ["Module1"],
                    ],
                    "expectedCycles" => [
                          ["Module1", "Module2", "Module3"],
                    ],
              ],

              "Two Connected Cycles" => [
                    "neighbourhoods" => [
                          "Module1" => ["Module2"],
                          "Module3" => ["Module2"],
                          "Module2" => ["Module1", "Module3"],
                    ],
                    "expectedCycles" => [
                          ["Module1", "Module2"],
                          ["Module2", "Module3"],
                    ],
              ],

              "Two Disconnected Cycles" => [
                    "neighbourhoods" => [
                          "Module1" => ["Module2"],
                          "Module2" => ["Module1"],
                          "Module3" => ["Module4"],
                          "Module4" => ["Module3"],
                    ],
                    "expectedCycles" => [
                          ["Module1", "Module2"],
                          ["Module3", "Module4"],
                    ],
              ],

              "Non-alphabetical Cycle" => [
                    "neighbourhoods" => [
                          "Module1" => ["Module3"],
                          "Module2" => ["Module1"],
                          "Module3" => ["Module2"],
                    ],
                    "expectedCycles" => [
                          ["Module1", "Module3", "Module2"],
                    ],
              ],
        ];
    }
}
