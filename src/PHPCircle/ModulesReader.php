<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle;


use function array_merge;
use function array_unique;
use function array_values;
use function is_dir;
use Koriit\PHPCircle\Graph\DirectedGraph;
use Koriit\PHPCircle\Graph\Vertex;
use Koriit\PHPCircle\Tokenizer\DependenciesReader;
use Koriit\PHPCircle\Tokenizer\Exceptions\MalformedFile;
use const PHP_EOL;
use function preg_match;
use function print_r;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use RuntimeException;

class ModulesReader
{
    /**
     * @var DependenciesReader
     */
    private $fileReader;

    public function __construct(DependenciesReader $fileReader)
    {
        $this->fileReader = $fileReader;
    }

    /**
     * @param Module[] $modules
     *
     * @return DirectedGraph Graph describing dependencies between modules
     * @throws MalformedFile
     */
    public function generateDependenciesGraph(array $modules) {
        /** @var Vertex[] $vertices */
        $vertices = [];
        foreach ($modules as $module) {
            $vertices[] = new Vertex($module);
        }

        foreach ($vertices as $vertex) {
            $dependencies = $this->findModuleDependencies($vertex->getValue()->getPath());
            foreach($dependencies as $dependency) {
                foreach ($vertices as $neighbour) {
                    if ($vertex !== $neighbour && preg_match($neighbour->getValue()->getPattern(), $dependency)) {
                        $vertex->addNeighbour($neighbour);
                    }
                }
            }
        }

        return new DirectedGraph($vertices);
    }

    /**
     * @param string $modulePath Path to module, either file or directory
     *
     * @return string[] List of module's dependencies
     * @throws MalformedFile
     */
    public function findModuleDependencies($modulePath)
    {
        $dependencies = [];
        $files = $this->findPHPFiles($modulePath);
        foreach ($files as $file) {
            $dependencies = array_merge($dependencies, $this->fileReader->findFileDependencies($file));
        }

        // remove duplicates
        $dependencies = array_unique($dependencies);

        // reindex array
        return array_values($dependencies);
    }

    /**
     * @param string $modulePath
     *
     * @return string[] Paths to PHP files in the module
     */
    private function findPHPFiles($modulePath)
    {
        $files = [];

        if (is_dir($modulePath)) {
            $dirIterator = new RecursiveDirectoryIterator($modulePath);
            $iterator = new RecursiveIteratorIterator($dirIterator);
            foreach ($iterator as $file) {
                if (preg_match("/\.php$/i", $file)) {
                    $files[] = (string) $file;
                }
            }

        } else if (is_file($modulePath)) {
            $files[] = $modulePath;

        } else {
            throw new RuntimeException("Module cannot be read or does not exist: " . $modulePath);
        }

        return $files;
    }
}
