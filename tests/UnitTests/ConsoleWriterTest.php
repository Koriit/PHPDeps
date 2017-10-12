<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Test\UnitTests;

use Koriit\PHPCircle\Console\ConsoleWriter;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use const PHP_EOL;

class ConsoleWriterTest extends \PHPUnit_Framework_TestCase
{
    /** @var BufferedOutput */
    private $output;

    /** @var ConsoleWriter */
    private $writer;

    public function setUp() {
        $this->output = new BufferedOutput();
        $this->writer = new ConsoleWriter($this->output);
    }

    /**
     * @test
     * @dataProvider outputCases
     *
     * @param string[] $nodes          Nodes to write
     * @param string[] $expectedOutput Lines of expected output
     */
    public function verifyOutput(array $nodes, $expectedOutput)
    {
        $this->writer->drawGraphCycle($nodes);
        $output = $this->output->fetch();

        $this->assertSame($expectedOutput, $output);
    }

    public function outputCases()
    {
        return [
              "TwoNodes"   => [
                    ["A", "B"],
                    "* A" . PHP_EOL .
                    "↕" . PHP_EOL .
                    "* B" . PHP_EOL,
              ],

              "ThreeNodes" => [
                    ["A", "B", "C"],
                    "* A" . PHP_EOL .
                    "↑↘" . PHP_EOL .
                    "↑ * B" . PHP_EOL .
                    "↑↙" . PHP_EOL .
                    "* C" . PHP_EOL,
              ],

              "FourNodes"  => [
                    ["A", "B", "C", "D"],
                    "* A" . PHP_EOL .
                    "↑↘" . PHP_EOL .
                    "↑ * B" . PHP_EOL .
                    "↑ ↓" . PHP_EOL .
                    "↑ * C" . PHP_EOL .
                    "↑↙" . PHP_EOL .
                    "* D" . PHP_EOL,
              ],
        ];
    }
}
