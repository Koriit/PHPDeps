<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

use Vendor\Library\Module1\SomeClass;
use Vendor\Library\Module2\Package\SomeTrait;

trait FakeTrait {
    use SomeTrait;

    public function someMethod() {
        return true;
    }
}
