<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Graph;


use function in_array;

class Vertex
{
    /** @var mixed */
    private $value;

    /** @var Vertex[] */
    private $neighbours;

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
     * @param Vertex[] $neighbours
     */
    public function setNeighbours(array $neighbours)
    {
        $this->neighbours = $neighbours;
    }

    /**
     * @param Vertex $neighbour
     */
    public function addNeighbour(Vertex $neighbour)
    {
        if (!in_array($neighbour, $this->neighbours)) {
            $this->neighbours[] = $neighbour;
        }
    }
}
