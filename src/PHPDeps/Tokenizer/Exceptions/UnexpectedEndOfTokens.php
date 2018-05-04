<?php

namespace Koriit\PHPDeps\Tokenizer\Exceptions;

use Exception;

class UnexpectedEndOfTokens extends Exception
{
    public function __construct($cause = null)
    {
        parent::__construct('Unexpected end of tokens', 0, $cause);
    }
}
