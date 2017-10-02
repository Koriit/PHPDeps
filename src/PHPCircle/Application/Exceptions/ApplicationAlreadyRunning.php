<?php

namespace Koriit\PHPCircle\Application\Exceptions;

use Throwable;

class ApplicationAlreadyRunning extends ApplicationLifecycleException
{
    public function __construct(Throwable $cause = null)
    {
        parent::__construct("Application is already running", 0, $cause);
    }
}
