<?php

namespace Koriit\PHPCircle\Tokenizer\Exceptions;

use Exception;
use function is_array;
use Throwable;

class UnexpectedToken extends Exception
{
    /**
     * @var mixed
     */
    private $token;

    public function __construct($token, Throwable $cause = null)
    {
        parent::__construct("Unexpected token: " . (is_array($token) ? $token[1] : $token), 0, $cause);

        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }
}
