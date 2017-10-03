<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Config;


class DirModule
{
    /** @var string */
    private $name;

    /** @var string */
    private $namespace;

    /** @var string */
    private $path;

    public function __construct($name, $namespace, $path)
    {
        $this->name = $name;
        $this->namespace = $namespace;
        $this->path = $path;
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
}
