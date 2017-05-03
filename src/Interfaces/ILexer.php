<?php

namespace ArithmeticExpressions\Interfaces;

/**
 * Lexical analyser interface.
 *
 * @author Sergey Milimko <smilimko@gmail.com>
 * @version 0.0.1
 */
interface ILexer
{
    /**
     * Returns iterator over all tokens.
     * Each token represents a numeric array of four elements:
     * [token number, token value, line, column]
     *
     * @return \Generator
     * @throws \ArithmeticExpressions\Exceptions\LexerException
     */
    public function getTokens() : \Generator;
}