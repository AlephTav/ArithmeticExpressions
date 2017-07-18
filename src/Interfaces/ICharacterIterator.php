<?php

namespace ArithmeticExpressions\Interfaces;

/**
 * Interface for any character iterator.
 */
interface ICharacterIterator extends \IteratorAggregate, \Countable
{
    /**
     * Returns the current line number or NULL on failure
     *
     * @return int
     */
    public function getLine();

    /**
     * Returns the current column number or NULL on failure.
     *
     * @return int
     */
    public function getColumn();

    /**
     * Prepares the iterator for forward iterations.
     *
     * @return void
     */
    public function rewindForward();

    /**
     * Prepares the iterator for backward iterations.
     *
     * @return void
     */
    public function rewindBackward();

    /**
     * Returns the forward iterator.
     *
     * @return \Generator
     */
    public function getForwardIterator();

    /**
     * Returns the backward iterator.
     *
     * @return \Generator
     */
    public function getBackwardIterator();

    /**
     * Returns the current character or empty string on failure.
     *
     * @return string
     */
    public function getChar() : string;

    /**
     * Returns the position of the current character or NULL on failure.
     *
     * @return int
     */
    public function getIndex();

    /**
     * Returns TRUE if the internal pointer within the character
     * sequence and FALSE otherwise.
     *
     * @return bool
     */
    public function isValid() : bool;

    /**
     * Advances the internal pointer to the next character and return it
     * (or empty string on failure).
     *
     * @return string
     */
    public function getNextChar() : string;

    /**
     * Moves the internal pointer to the previous character and return it
     * (or empty string on failure).
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

    /**
     * Clear all stored states.
     *
     * @return void
     */
    public function clear();
}