<?php
/**
 * @copyright 2018 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPDeps\Tokenizer;

use Koriit\PHPDeps\Tokenizer\Exceptions\MalformedFile;
use Koriit\PHPDeps\Tokenizer\Exceptions\UnexpectedToken;
use Koriit\PHPDeps\Tokenizer\Exceptions\UnexpectedEndOfTokens;
use Koriit\PHPDeps\Tokenizer\Exceptions\WrongPosition;

class DependenciesReader
{
    /**
     * @param string $filePath Path to file to parse
     *
     * @throws MalformedFile
     *
     * @return string[] Real dependencies read from use statements
     */
    public function findFileDependencies($filePath)
    {
        $tokens = TokensIterator::fromFile($filePath);

        $useDependencies = [];

        try {
            while ($tokens->valid()) {
                if ($tokens->currentIsOneOf([T_CLASS, T_TRAIT, T_FUNCTION, T_INTERFACE])) {
                    $tokens->skipNextBlock();
                } elseif ($tokens->currentIs(T_USE)) {
                    $useDependencies[] = $this->readUseStatement($tokens);
                }

                $tokens->next();
            }
        } catch (UnexpectedEndOfTokens $e) {
            throw new MalformedFile($filePath, $e);
        } catch (UnexpectedToken $e) {
            throw new MalformedFile($filePath, $e);
        }

        return $useDependencies;
    }

    /**
     * @param TokensIterator $it
     *
     * @throws UnexpectedEndOfTokens
     * @throws UnexpectedToken
     *
     * @return string Real dependency from use statement
     */
    private function readUseStatement(TokensIterator $it)
    {
        if (!$it->valid() || !$it->currentIs(T_USE)) {
            throw new WrongPosition('Not at use statement position.');
        }

        $it->skipTokensIfPresent([T_USE, T_WHITESPACE, T_CONST, T_FUNCTION]);

        $dependency = '';
        while ($it->valid()) {
            $token = $it->current();
            if ($token == ';' || $it->currentIs(T_AS)) {
                return $dependency;
            } elseif ($it->currentIsOneOf([T_STRING, T_NS_SEPARATOR])) {
                $dependency .= $token[1];
            } elseif (!$it->currentIs(T_WHITESPACE)) {
                throw new UnexpectedToken($token);
            }

            $it->next();
        }

        throw new UnexpectedEndOfTokens();
    }
}
