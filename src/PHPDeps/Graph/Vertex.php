<?php
/**
 * @copyright 2018 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPDeps\Graph;

class Vertex
{
    /** @var mixed Held value */
    private $value;

    /** @var Vertex[] */
    private $neighbours;

    /** @var int The index in the graph */
    private $index = null;

    public function __construct($value, array $neighbours = [])
    {
        $this->value = $value;
        $this->neighbours = $neighbours;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getNeighbours()
    {
        return $this->neighbours;
    }

    /**
     * @param Vertex $neighbour
     */
    public function addNeighbour(Vertex $neighbour)
    {
        if (!\in_array($neighbour, $this->neighbours)) {
            $this->neighbours[] = $neighbour;
        }
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function setIndex($index)
    {
        $this->index = $index;
    }
}
