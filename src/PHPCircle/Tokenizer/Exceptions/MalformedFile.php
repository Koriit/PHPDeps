<?php

namespace Koriit\PHPCircle\Tokenizer\Exceptions;

use Exception;
use Throwable;

class MalformedFile extends Exception
{
    /**
     * @var string
     */
    private $filePath;

    public function __construct($filePath, Throwable $cause = null)
    {
        parent::__construct("Malformed file: " . $filePath, 0, $cause);

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
