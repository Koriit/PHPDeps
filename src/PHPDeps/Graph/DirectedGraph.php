<?php
/**
 * @copyright 2018 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPDeps\Graph;

class DirectedGraph
{
    /** @var Vertex[] */
    private $vertices = [];

    public function __construct(array $vertices)
    {
        // Remove duplicates
        $this->vertices = \array_unique($vertices, SORT_REGULAR);

        $this->reindex();
    }

    public function getVertices()
    {
        return $this->vertices;
    }

    /**
     * @param callable|null $comparator Comparator of vertex values
     *
     * @return array
     */
    public function findAllCycles(callable $comparator = null)
    {
        $cycles = [];

        $verticesCount = \count($this->vertices);
        foreach ($this->vertices as $vertex) {
            $visited = [];
            for ($i = 0; $i < $verticesCount; $i++) {
                $visited[$i] = false;
            }

            foreach ($vertex->getNeighbours() as $neighbour) {
                $foundCycles = $this->findAllCyclesRecursive($vertex, $neighbour, $visited);
                if ($foundCycles) {
                    $cycles = \array_merge($cycles, $foundCycles);
                }
            }
        }

        return $this->sortAndRemoveDuplicateCycles($cycles, $comparator);
    }

    /**
     * @param Vertex  $needle
     * @param Vertex  $current
     * @param bool[]  $visited
     * @param mixed[] $currentCycle
     *
     * @return array|bool
     */
    private function findAllCyclesRecursive(Vertex $needle, Vertex $current, &$visited, $currentCycle = [])
    {
        if ($visited[$current->getIndex()]) {
            return false;
        }

        $currentCycle[] = $current->getValue();
        if ($current === $needle) {
            return [$currentCycle];
        }

        $cycles = [];
        $visited[$current->getIndex()] = true;
        foreach ($current->getNeighbours() as $neighbour) {
            $foundCycles = $this->findAllCyclesRecursive($needle, $neighbour, $visited, $currentCycle);
            if ($foundCycles) {
                $cycles = \array_merge($cycles, $foundCycles);
            }
        }

        return $cycles;
    }

    private function reindex()
    {
        $this->vertices = \array_values($this->vertices);
        $verticesCount = \count($this->vertices);
        for ($i = 0; $i < $verticesCount; $i++) {
            $this->vertices[$i]->setIndex($i);
        }
    }

    /**
     * @param mixed[][]     $cycles     Array of cycles
     * @param callable|null $comparator Comparator of vertex values
     *
     * @return mixed
     */
    private function sortAndRemoveDuplicateCycles(array $cycles, callable $comparator = null)
    {
        if ($comparator === null) {
            $comparator = $this->getDefaultComparator();
        }

        $cyclesCount = \count($cycles);
        for ($i = 0; $i < $cyclesCount; $i++) {
            $this->scrollCycle($cycles[$i], $comparator);

            for ($j = 0; $j < $cyclesCount; $j++) {
                if ($i == $j || !isset($cycles[$j])) {
                    continue;
                }
                if ($cycles[$i] === $cycles[$j]) {
                    unset($cycles[$i]);
                    break;
                }
            }
        }

        // Sort the cycles themselves
        \usort($cycles, $comparator);

        // Reindex array
        return \array_values($cycles);
    }

    /**
     * @param array    $array
     * @param callable $comparator
     */
    private function scrollCycle(array &$array, callable $comparator)
    {
        $count = \count($array);
        // Find minimal element
        $firstPos = 0;
        for ($i = 1; $i < $count; $i++) {
            if ($comparator($array[$firstPos], $array[$i]) === 1) {
                $firstPos = $i;
            }
        }

        // Push the elements to the end of array until minimal element is at first position
        for ($i = 0; $i < $firstPos; $i++) {
            \array_push($array, \array_shift($array));
        }
    }

    /**
     * @return callable
     */
    private function getDefaultComparator()
    {
        return function ($a, $b) {
            return $a > $b ? 1 : ($a < $b ? -1 : 0);
        };
    }
}
