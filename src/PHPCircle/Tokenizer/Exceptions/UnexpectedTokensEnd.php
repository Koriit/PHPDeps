<?php

namespace Koriit\PHPCircle\Tokenizer\Exceptions;

use Exception;
use Throwable;

class UnexpectedTokensEnd extends Exception
{
    public function __construct(Throwable $cause = null)
    {
        parent::__construct("Unexpected end of tokens", 0, $cause);
    }
}
