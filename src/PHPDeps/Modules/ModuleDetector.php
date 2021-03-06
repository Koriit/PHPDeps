<?php
/**
 * @copyright 2018 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPDeps\Modules;

class ModuleDetector
{
    /** @var string */
    private $namespace;

    /** @var string */
    private $path;

    public function __construct($namespace, $path)
    {
        $this->namespace = $namespace;
        $this->path = $path;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return Module[]
     */
    public function findModules()
    {
        $modules = [];

        foreach (\glob($this->path . '/*', GLOB_ONLYDIR) as $modulePath) {
            $name = \basename($modulePath);
            $modules[] = new Module($name, $this->namespace . '\\' . $name, $modulePath);
        }

        return $modules;
    }
}
