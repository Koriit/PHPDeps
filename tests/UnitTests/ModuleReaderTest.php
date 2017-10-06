<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Test\UnitTests;


use Koriit\PHPCircle\ModuleReader;
use Koriit\PHPCircle\Tokenizer\DependenciesReader;
use Koriit\PHPCircle\Tokenizer\Exceptions\MalformedFile;
use PHPUnit_Framework_TestCase;
use function print_r;

class ModuleReaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ModuleReader
     */
    private $reader;

    public function setUp()
    {
        $this->reader = new ModuleReader(new DependenciesReader());
    }

    /**
     * @test
     */
    public function shouldProperlyReadDirModuleDependencies()
    {
        $expected = [
              "Vendor\Library2\Module\AnotherClass",
              "Vendor\Library\Module1\SomeClass",
              "Vendor\Library\Module2\Package\SomeOtherClass",
              "Vendor\Library\Module3\Package\SomeYetAnotherClass",
              "Vendor\Library2\Module\SomeClass",
        ];

        $module = __DIR__ . "/../Cases/Modules/DirModule";

        $dependencies = $this->reader->findDependencies($module);

        $this->assertEquals($expected, $dependencies, '', 0.0, 10, true);
    }

    /**
     * @test
     *
     * @dataProvider casesProvider
     *
     * @param array  $expectedList
     * @param string $classFile
     *
     * @throws MalformedFile
     */
//    public function shouldHandleAllCases($expectedList, $classFile)
//    {
//        $resultList = $this->reader->findDependencies($classFile);
//
//        $this->assertEquals($expectedList, $resultList);
//    }


//    public function casesProvider()
//    {
//        return [
//              'Simple Case' => [
//                    [
//                          'Vendor\Library\Module1\SomeClass',
//                          'Vendor\Library\Module2\Package\SomeOtherClass',
//                    ],
//                    __DIR__ . '/../Cases/UseStatements/SimpleCase.php',
//              ],
//        ];
//    }
}
