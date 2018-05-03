<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPDeps\Modules;

use Koriit\PHPDeps\Graph\DirectedGraph;
use Koriit\PHPDeps\Graph\Vertex;
use Koriit\PHPDeps\Modules\Exceptions\ModuleNotFound;
use Koriit\PHPDeps\Tokenizer\DependenciesReader;
use Koriit\PHPDeps\Tokenizer\Exceptions\MalformedFile;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

class ModuleReader
{
    /** @var DependenciesReader */
    private $fileReader;

    public function __construct(DependenciesReader $fileReader)
    {
        $this->fileReader = $fileReader;
    }

    /**
     * @param Module[] $modules
     *
     * @throws MalformedFile
     * @throws ModuleNotFound
     *
     * @return DirectedGraph Graph describing dependencies between modules
     */
    public function generateDependenciesGraph(array $modules)
    {
        /** @var Vertex[] $vertices */
        $vertices = [];
        foreach ($modules as $module) {
            $vertices[] = new Vertex($module);
        }

        foreach ($vertices as $vertex) {
            $dependencies = $this->findModuleDependencies($vertex->getValue()->getPath());
            foreach ($dependencies as $dependency) {
                foreach ($vertices as $neighbour) {
                    if ($vertex !== $neighbour && \preg_match($neighbour->getValue()->getPattern(), $dependency)) {
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
     * @throws MalformedFile
     * @throws ModuleNotFound
     *
     * @return string[] List of module's dependencies
     */
    public function findModuleDependencies($modulePath)
    {
        $dependencies = [];
        $files = $this->findPHPFiles($modulePath);
        foreach ($files as $file) {
            $dependencies = \array_merge($dependencies, $this->fileReader->findFileDependencies($file));
        }

        // remove duplicates
        $dependencies = \array_unique($dependencies);

        // reindex array
        return \array_values($dependencies);
    }

    /**
     * @param string $modulePath
     *
     * @return string[] Paths to PHP files in the module
     * @throws ModuleNotFound
     */
    private function findPHPFiles($modulePath)
    {
        $files = [];

        if (\is_dir($modulePath)) {
            $dirIterator = new RecursiveDirectoryIterator($modulePath);
            $iterator = new RecursiveIteratorIterator($dirIterator);
            foreach ($iterator as $file) {
                if (\preg_match("/\.php$/i", $file)) {
                    $files[] = (string) $file;
                }
            }
        } elseif (\is_file($modulePath)) {
            $files[] = $modulePath;
        } else {
            throw new ModuleNotFound($modulePath);
        }

        return $files;
    }
}
