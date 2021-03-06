<?php
/**
 * @copyright 2018 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPDeps\Tokenizer;

use ArrayIterator;
use Koriit\PHPDeps\Tokenizer\Exceptions\UnexpectedEndOfTokens;
use Koriit\PHPDeps\Tokenizer\Exceptions\WrongPosition;

class TokensIterator extends ArrayIterator
{
    /**
     * @param mixed $token
     * @param int   $tokenType
     *
     * @return bool
     */
    public static function isToken($token, $tokenType)
    {
        return \is_array($token) && $token[0] === $tokenType;
    }

    /**
     * @param mixed $token
     * @param int[] $tokenTypes
     *
     * @return bool
     */
    public static function isOneOfTokens($token, array $tokenTypes)
    {
        return \is_array($token) && \in_array($token[0], $tokenTypes, true);
    }

    /**
     * @param string $contents Contents of PHP file to tokenize
     *
     * @return TokensIterator
     */
    public static function fromContents($contents)
    {
        return new static(\token_get_all($contents));
    }

    /**
     * @param string $filePath Path to PHP file to tokenize
     *
     * @return TokensIterator
     */
    public static function fromFile($filePath)
    {
        return static::fromContents(\file_get_contents($filePath));
    }

    /**
     * @throws UnexpectedEndOfTokens
     */
    public function skipNextBlock()
    {
        $this->findNextBlock();
        $this->skipBlock();
    }

    /**
     * @throws UnexpectedEndOfTokens
     */
    public function findNextBlock()
    {
        while ($this->valid()) {
            if ($this->current() === '{') {
                return;
            }

            $this->next();
        }

        throw new UnexpectedEndOfTokens();
    }

    /**
     * @throws UnexpectedEndOfTokens
     */
    public function skipBlock()
    {
        if (!$this->valid() || $this->current() !== '{') {
            throw new WrongPosition('Not at block beginning position.');
        }

        $bracesCounter = 1;
        $this->next();
        while ($this->valid() && $bracesCounter > 0) {
            if ($this->current() === '{') {
                $bracesCounter++;
            } elseif ($this->current() === '}') {
                $bracesCounter--;
            }

            $this->next();
        }

        if ($bracesCounter != 0) {
            throw new UnexpectedEndOfTokens();
        }
    }

    public function skipWhitespaces()
    {
        $this->skipTokensIfPresent([T_WHITESPACE]);
    }

    public function skipTokensIfPresent(array $tokenTypes)
    {
        while ($this->valid() && $this->currentIsOneOf($tokenTypes)) {
            $this->next();
        }
    }

    /**
     * @param int $tokenType
     *
     * @return bool
     */
    public function currentIs($tokenType)
    {
        return static::isToken($this->current(), $tokenType);
    }

    /**
     * @param int[] $tokenTypes
     *
     * @return bool
     */
    public function currentIsOneOf(array $tokenTypes)
    {
        return static::isOneOfTokens($this->current(), $tokenTypes);
    }
}
