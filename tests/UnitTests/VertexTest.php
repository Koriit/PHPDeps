<?php
/**
 * @copyright 2018 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPDeps\Test\UnitTests;

use Koriit\PHPDeps\Graph\Vertex;
use PHPUnit_Framework_TestCase;

class VertexTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldAddNeighbourOnlyOnce()
    {
        $vertex = new Vertex('Vertex');
        $neighbour = new Vertex('Neighbour');

        // Add first time
        $vertex->addNeighbour($neighbour);
        $this->assertCount(1, $vertex->getNeighbours());

        // Add second time
        $vertex->addNeighbour($neighbour);
        $this->assertCount(1, $vertex->getNeighbours());
    }
}
