<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Tokenizer;


use Koriit\PHPCircle\Tokenizer\Exceptions\UnexpectedFileEnd;
use Koriit\PHPCircle\Tokenizer\Exceptions\UnexpectedTokensEnd;
use Koriit\PHPCircle\Tokenizer\Exceptions\WrongPosition;
use const T_CLASS;
use const T_CONST;
use const T_FUNCTION;
use const T_TRAIT;
use const T_USE;
use const T_WHITESPACE;
use function is_array;

class DependenciesReader
{
    /**
     * @param string $filePath Path to file to parse
     *
     * @return string[] Real dependencies read from use statements
     * @throws UnexpectedFileEnd
     */
    public function findDependencies($filePath)
    {
        $tokens = TokensIterator::fromFile($filePath);

        $useDependencies = [];
        try {
            while ($tokens->valid()) {
                if ($tokens->currentIsOneOf([T_CLASS, T_TRAIT, T_FUNCTION])) {
                    $tokens->skipNextBlock();

                } else if ($tokens->currentIs(T_USE)) {
                    $useDependencies[] = $this->readUseStatement($tokens);
                }

                $tokens->next();
            }

        } catch (UnexpectedTokensEnd $e) {
            throw new UnexpectedFileEnd($filePath, $e);
        }

        return $useDependencies;
    }

    /**
     * @param TokensIterator $it
     *
     * @return string Real dependency from use statement
     * @throws UnexpectedTokensEnd
     */
    private function readUseStatement(TokensIterator $it)
    {
        if (!$it->valid() || !$it->currentIs(T_USE)) {
            throw new WrongPosition("Not at use statement position.");
        }

        $it->next();
        $dependency = "";
        while ($it->valid()) {
            $token = $it->current();
            if ($token == ';' || $it->currentIs(T_AS)) {
                return $dependency;

            } else if (!$it->currentIsOneOf([T_WHITESPACE, T_CONST, T_FUNCTION])) {
                $dependency .= is_array($token) ? $token[1] : $token;
            }

            $it->next();
        }

        throw new UnexpectedTokensEnd();
    }
}
