<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Test\UnitTests;


use Koriit\PHPCircle\Tokenizer\DependenciesReader;
use Koriit\PHPCircle\Tokenizer\Exceptions\UnexpectedFileEnd;
use PHPUnit_Framework_TestCase;

class DependenciesReaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DependenciesReader
     */
    private $reader;

    public function setUp()
    {
        $this->reader = new DependenciesReader();
    }

    /**
     * @test
     *
     * @dataProvider casesProvider
     *
     * @param array  $expectedList
     * @param string $classFile
     *
     * @throws UnexpectedFileEnd
     */
    public function shouldHandleAllCases($expectedList, $classFile)
    {
        $resultList = $this->reader->findDependencies($classFile);

        $this->assertEquals($expectedList, $resultList);
    }


    public function casesProvider()
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

              'Empty Case' => [
                    [],
                    __DIR__ . '/../Cases/UseStatements/EmptyCase.php',
              ],
        ];
    }
}
