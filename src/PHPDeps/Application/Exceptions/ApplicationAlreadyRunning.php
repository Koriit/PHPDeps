<?php

namespace Koriit\PHPDeps\Application\Exceptions;

class ApplicationAlreadyRunning extends ApplicationLifecycleException
{
    public function __construct($cause = null)
    {
        parent::__construct('Application is already running', 0, $cause);
    }
}
