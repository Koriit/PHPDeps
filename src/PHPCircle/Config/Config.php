<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Config;


use Koriit\PHPCircle\Module;

class Config
{
    /** @var Module[] */
    private $modules;

    /** @var DirDetector[] */
    private $dirDetectors;

    /**
     * @param Module[]      $modules
     * @param DirDetector[] $dirDetectors
     */
    public function __construct(array $modules, array $dirDetectors)
    {
        $this->modules = $modules;
        $this->dirDetectors = $dirDetectors;
    }

    public function getModules()
    {
        return $this->modules;
    }

    public function getDirDetectors()
    {
        return $this->dirDetectors;
    }
}
