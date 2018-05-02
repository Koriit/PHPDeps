<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPDeps\Config\Exceptions;

use Exception;

class InvalidSchema extends Exception
{
    public function __construct($cause = null)
    {
        parent::__construct('File does not pass schema validation', 0, $cause);
    }
}
