<?php

namespace Koriit\PHPDeps\Tokenizer\Exceptions;

use Exception;

class MalformedFile extends Exception
{
    /**
     * @var string
     */
    private $filePath;

    public function __construct($filePath, $cause = null)
    {
        parent::__construct('Malformed file: ' . $filePath, 0, $cause);

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
