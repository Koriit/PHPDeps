<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Config;

use Koriit\PHPCircle\Modules\Module;
use Koriit\PHPCircle\Modules\ModuleDetector;

class Config
{
    /** @var Module[] */
    private $modules;

    /** @var ModuleDetector[] */
    private $moduleDetectors;

    /**
     * @param Module[]         $modules
     * @param ModuleDetector[] $moduleDetectors
     */
    public function __construct(array $modules, array $moduleDetectors)
    {
        $this->modules = $modules;
        $this->moduleDetectors = $moduleDetectors;
    }

    public function getModules()
    {
        return $this->modules;
    }

    public function getModuleDetectors()
    {
        return $this->moduleDetectors;
    }
}
