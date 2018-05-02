<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPDeps\Modules;

class Module
{
    /** @var string */
    private $name;

    /** @var string */
    private $namespace;

    /** @var string */
    private $path;

    /** @var string */
    private $pattern;

    public function __construct($name, $namespace, $path)
    {
        $this->name = $name;
        $this->namespace = $namespace;
        $this->path = $path;
        $this->pattern = '/^' . \str_replace('\\', '\\\\', $namespace) . (\is_dir($path) ? '(\\\\.+)?' : '') . '$/';
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getPattern()
    {
        return $this->pattern;
    }
}
