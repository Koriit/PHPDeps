<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Console;

use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class GraphWriter
{
    /** @var OutputInterface */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param string[] $nodes
     */
    public function drawGraphCycle(array $nodes)
    {
        if (count($nodes) < 2) {
            throw new RuntimeException('Not enough nodes to draw a cycle');
        }

        $firstNode = array_shift($nodes);
        $lastNode = array_pop($nodes);

        $this->drawNode($firstNode);

        if (!empty($nodes)) {
            $this->drawLine('↑↘');
            $i = 0;
            foreach ($nodes as $node) {
                if ($i++) {
                    $this->drawLine('↑ ↓');
                }
                $this->drawLine('↑', false);
                $this->drawNode($node);
            }
            $this->drawLine('↑↙');
        } else {
            $this->drawLine('↕');
        }

        $this->drawNode($lastNode);
    }

    /**
     * @param string $node
     */
    private function drawNode($node)
    {
        $this->output->writeln('<fg=red>*</> ' . $node);
    }

    /**
     * @param string $line
     * @param bool   $newLine
     */
    private function drawLine($line, $newLine = true)
    {
        $formatted = '<fg=yellow>' . $line . '</>';

        if ($newLine) {
            $this->output->writeln($formatted);
        } else {
            // Draw additional space as we are not going to new line
            $this->output->write($formatted . ' ');
        }
    }
}
