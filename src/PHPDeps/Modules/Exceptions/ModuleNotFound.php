<?php

namespace Koriit\PHPDeps\Modules\Exceptions;

use Exception;

class ModuleNotFound extends Exception
{
    /**
     * @var string
     */
    private $modulePath;

    public function __construct($modulePath, $cause = null)
    {
        parent::__construct('Module cannot be read or does not exist: ' . $modulePath, 0, $cause);

        $this->modulePath = $modulePath;
    }

    /**
     * @return string
     */
    public function getModulePath()
    {
        return $this->modulePath;
    }
}
