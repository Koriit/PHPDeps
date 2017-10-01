<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Events;

/**
 * Event dispatched when application has executed requested command.
 */
class AppExecutedCommandEvent
{
    /**
     * @var int
     */
    private $exitCode;

    /**
     * @param int $exitCode
     */
    public function __construct($exitCode)
    {
        $this->exitCode = $exitCode;
    }

    /**
     * @return int
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }
}
