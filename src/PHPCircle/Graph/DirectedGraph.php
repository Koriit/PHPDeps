<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Graph;


class DirectedGraph
{
    /** @var Vertex[] */
    private $vertices;

    public function __construct(array $vertices)
    {
        $this->vertices = $vertices;
    }

    public function getVertices()
    {
        return $this->vertices;
    }
}