<?php
/**
 * @copyright 2018 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

use Vendor\Library\Module\SomeClass;

interface FakeInterface
{
    /*
     * In this case we check that file parser can handle T_FUNCTION token without {} block.
     */
    public function someMethod(SomeClass $someObject);
}
