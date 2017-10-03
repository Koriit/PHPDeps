<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Config;


class DirDetector
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
}
