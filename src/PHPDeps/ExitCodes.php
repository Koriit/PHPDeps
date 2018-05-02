<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPDeps;

class ExitCodes
{
    const OK = 0;
    const UNEXPECTED_ERROR = 1;
    const SHELL_MISUSE = 2;
    const CIRCULAR_DEPENDENCIES_EXIST = 3;
    const STATUS_OUT_OF_RANGE = 255;
}
