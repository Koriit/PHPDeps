<?php

namespace Koriit\PHPCircle\Tokenizer\Exceptions;

use Exception;

class UnexpectedTokensEnd extends Exception
{
    public function __construct($cause = null)
    {
        parent::__construct("Unexpected end of tokens", 0, $cause);
    }
}
