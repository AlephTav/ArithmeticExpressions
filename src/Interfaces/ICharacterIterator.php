<?php

namespace ArithmeticExpressions\Interfaces;

/**
 * Interface for any character iterator.
 */
interface ICharacterIterator extends \Iterator, \Countable
{
    /**
     * Returns the next character or empty string on failure.
     *
     * @return string
     */
    public function getNextChar() : string;

    /**
     * Returns the previous character or empty string on failure.
     *
     * @return string
     */
    public function getPrevChar() : string;

    /**
     * Remembers the current state of the iterator.
     *
     * @param string $key The state name.
     * @return void
     */
    public function remember(string $key);

    /**
     * Restores the previously saved state of the iterator.
     *
     * @param string $key The state name.
     * @return void
     */
    public function restore(string $key);
}