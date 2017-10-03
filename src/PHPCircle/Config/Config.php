<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Config;


class Config
{
    /** @var DirModule[] */
    private $dirModules;

    /** @var ClassModule[] */
    private $classModules;

    /** @var FileModule[] */
    private $fileModules;

    /** @var DirDetector[] */
    private $dirDetectors;

    /**
     * @param DirModule[]   $dirModules
     * @param ClassModule[] $classModules
     * @param FileModule[]  $fileModules
     * @param DirDetector[] $dirDetectors
     */
    public function __construct(array $dirModules, array $classModules, array $fileModules, array $dirDetectors)
    {
        $this->dirModules = $dirModules;
        $this->classModules = $classModules;
        $this->fileModules = $fileModules;
        $this->dirDetectors = $dirDetectors;
    }

    public function getDirModules()
    {
        return $this->dirModules;
    }

    public function getClassModules()
    {
        return $this->classModules;
    }

    public function getFileModules()
    {
        return $this->fileModules;
    }

    public function getDirDetectors()
    {
        return $this->dirDetectors;
    }
}
