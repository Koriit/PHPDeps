<?php
namespace Koriit\PHPCircle\Application;

/**
 * Describes interface of Application.
 */
interface ApplicationInterface
{
    /**
     * Runs the application.
     *
     * @see ApplicationLifecycle
     */
    public function run();

    /**
     * Checks if the application is already running.
     *
     * @return boolean
     */
    public function isRunning();

    /**
     * Returns application's name.
     *
     * @return string
     */
    public function getName();
}
