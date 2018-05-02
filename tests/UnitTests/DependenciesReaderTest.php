<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPDeps\Test\UnitTests;

use Koriit\PHPDeps\Tokenizer\DependenciesReader;
use Koriit\PHPDeps\Tokenizer\Exceptions\MalformedFile;
use PHPUnit_Framework_TestCase;

class DependenciesReaderTest extends PHPUnit_Framework_TestCase
{
    /** @var DependenciesReader */
    private $reader;

    public function setUp()
    {
        $this->reader = new DependenciesReader();
    }

    /**
     * @test
     *
     * @throws MalformedFile
     */
    public function shouldThrowWhenMalformedSyntax()
    {
        $this->setExpectedException(MalformedFile::class);

        $this->reader->findFileDependencies(__DIR__ . '/../Cases/UseStatements/MalformedSyntaxCase.php.txt');
    }

    /**
     * @test
     *
     * @throws MalformedFile
     */
    public function shouldThrowWhenFileInterrupted()
    {
        $this->setExpectedException(MalformedFile::class);

        $this->reader->findFileDependencies(__DIR__ . '/../Cases/UseStatements/InterruptedFileCase.php.txt');
    }

    /**
     * @test
     *
     * @dataProvider getFileCases
     *
     * @param array  $expectedList
     * @param string $classFile
     *
     * @throws MalformedFile
     */
    public function shouldHandleAllCases($expectedList, $classFile)
    {
        $resultList = $this->reader->findFileDependencies($classFile);

        $this->assertEquals($expectedList, $resultList);
    }

    public function getFileCases()
    {
        return [
              'Simple Case' => [
                    [
                          'Vendor\Library\Module1\SomeClass',
                          'Vendor\Library\Module2\Package\SomeOtherClass',
                    ],
                    __DIR__ . '/../Cases/UseStatements/SimpleCase.php',
              ],

              'Alias Case' => [
                    [
                          'Vendor\Library\Module1\SomeClass',
                          'Vendor\Library\Module2\Package\SomeOtherClass',
                          'Vendor\Library\Module2\Package\ClassA',
                    ],
                    __DIR__ . '/../Cases/UseStatements/AliasCase.php',
              ],

              'Class Trait Case' => [
                    [
                          'Vendor\Library\Module1\SomeClass',
                          'Vendor\Library\Module2\Package\SomeTrait',
                    ],
                    __DIR__ . '/../Cases/UseStatements/ClassTraitCase.php',
              ],

              'Trait Trait Case' => [
                    [
                          'Vendor\Library\Module1\SomeClass',
                          'Vendor\Library\Module2\Package\SomeTrait',
                    ],
                    __DIR__ . '/../Cases/UseStatements/TraitTraitCase.php',
              ],

              'Closure Inheriting Case' => [
                    [
                          'Vendor\Library\Module1\SomeClass',
                          'Vendor\Library\Module2\Package\SomeOtherClass',
                    ],
                    __DIR__ . '/../Cases/UseStatements/ClosureInheritingCase.php',
              ],

              'Interleaved Case' => [
                    [
                          'Vendor\Library\Module2\Package\SomeTrait',
                          'Vendor\Library\Module1\SomeClass',
                    ],
                    __DIR__ . '/../Cases/UseStatements/InterleavedCase.php',
              ],

              'Function Case' => [
                    [
                          'Vendor\Library\Module2\Package\SomeFunction',
                    ],
                    __DIR__ . '/../Cases/UseStatements/FunctionCase.php',
              ],

              'Const Case' => [
                    [
                          'Vendor\Library\Module2\Package\SomeConst',
                    ],
                    __DIR__ . '/../Cases/UseStatements/ConstCase.php',
              ],

              'Interface Case' => [
                    [
                          'Vendor\Library\Module\SomeClass',
                    ],
                    __DIR__ . '/../Cases/UseStatements/InterfaceCase.php',
              ],

              'Empty Case' => [
                    [],
                    __DIR__ . '/../Cases/UseStatements/EmptyCase.php',
              ],
        ];
    }
}
