<?php

namespace Koriit\PHPCircle\Tokenizer\Exceptions;

use Exception;
use Throwable;

class UnexpectedFileEnd extends Exception
{
    /**
     * @var string
     */
    private $filePath;

    public function __construct($filePath, Throwable $cause = null)
    {
        parent::__construct("Unexpected end of file: " . $filePath, 0, $cause);

        $this->filePath = $filePath;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }
}
