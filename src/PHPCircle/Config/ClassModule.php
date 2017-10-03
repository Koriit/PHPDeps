<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Config;


class ClassModule
{
    /** @var string */
    private $name;

    /** @var string */
    private $class;

    public function __construct($name, $class)
    {
        $this->name = $name;
        $this->class = $class;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getClass()
    {
        return $this->class;
    }
}
