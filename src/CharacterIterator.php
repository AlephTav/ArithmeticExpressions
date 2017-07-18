<?php
 
namespace ArithmeticExpressions;

use ArithmeticExpressions\Interfaces\ICharacterIterator;

/**
 * Use this class to iterate over all characters
 * in a string in two directions.
 *
 * @author Sergey Milimko <smilimko@gmail.com>
 * @version 0.0.1
 */
class CharacterIterator implements ICharacterIterator
{
    /**
     * A string of characters to iterate.
     *
     * @var string
     */     
    private $str = '';
    
    /**
     * The position of the current character.
     *
     * @var int
     */
    private $idx = -1;

    /**
     * The number of characters in the string.
     *
     * @var int
     */
    private $length = 0;
    
    /**
     * The current column.
     *
     * @var int
     */
    private $column = 0;

    /**
     * The current line.
     */     
    private $line = 1;
    
    /**
     * The new line character.
     *
     * @var string
     */
    private $newLineChar = "\n";

    /**
     * The current character.
     *
     * @var string
     */     
    private $char = '';
    
    /**
     * The saved states of the iterator.
     *
     * @var array
     */
    private $states = [];
    
    /**
     * Constructor.
     *
     * @param string $str A string of characters to iterate.
     * @param string $newLineChar The new line character.
     */
    public function __construct(string $str, string $newLineChar = "\n")
    {
        $this->str = $str;
        $this->length = strlen($str);
        $this->newLineChar = $newLineChar;
    }
    
    /**
     * Returns the number of characters.
     *
     * @return int
     */
    public function count() : int
    {
        return $this->length;
    }
    
    /**
     * Returns the current line number or NULL on failure
     *
     * @return int
     */
    public function getLine()
    {
        return $this->isValid() ? $this->line : null;
    }
    
    /**
     * Returns the current column number or NULL on failure.
     *
     * @return int
     */
    public function getColumn()
    {
        return $this->isValid() ? $this->column : null;
    }

    /**
     * Returns the forward iterator.
     *
     * @return \Generator
     */
    public function getIterator()
    {
        return $this->getForwardIterator();
    }

    /**
     * Returns the forward iterator.
     *
     * @return \Generator
     */
    public function getForwardIterator()
    {
        $this->rewindForward();
        while ('' !== $char = $this->getNextChar()) {
            yield $this->idx => $char;
        }
    }

    /**
     * Returns the backward iterator.
     *
     * @return \Generator
     */
    public function getBackwardIterator()
    {
        $this->rewindBackward();
        while ('' !== $char = $this->getPrevChar()) {
            yield $this->idx => $char;
        }
    }

    /**
     * Prepares the iterator for forward iterations.
     *
     * @return void
     */
    public function rewindForward()
    {
        $this->idx = -1;
        $this->line = 1;
        $this->column = 0;
        $this->char = '';
    }

    /**
     * Prepares the iterator for backward iterations.
     *
     * @return void
     */
    public function rewindBackward()
    {
        $this->idx = $this->length;
        $this->line = substr_count($this->str, $this->newLineChar) + 1;
        $this->column = $this->getColumnNumber($this->idx - ($this->str[$this->idx - 1] === $this->newLineChar));
        $this->char = '';
    }

    /**
     * Returns the current character or empty string on failure.
     *
     * @return string
     */
    public function getChar() : string
    {
        return $this->char;
    }

    /**
     * Returns the position of the current character or NULL on failure.
     *
     * @return int
     */
    public function getIndex()
    {
        return $this->isValid() ? $this->idx : null;
    }

    /**
     * Returns TRUE if the internal pointer within the character 
     * sequence and FALSE otherwise.
     *
     * @return bool
     */
    public function isValid() : bool
    {
        return $this->idx >= 0 && $this->idx < $this->length;
    }

    /**
     * Advances the internal pointer to the next character and return it
     * (or empty string on failure).
     *
     * @return string
     */
    public function getNextChar() : string
    {
        if ($this->idx < $this->length) {
            ++$this->idx;
            if ($this->idx < $this->length) {
                if ($this->char === $this->newLineChar) {
                    $this->column = 1;
                    ++$this->line;
                } else {
                    ++$this->column;
                }
                $this->char = $this->str[$this->idx];
            } else {
                if ($this->char === $this->newLineChar) {
                    ++$this->line;
                } else {
                    ++$this->column;
                }
                $this->char = '';
            }
        }
        return $this->char;
    }

    /**
     * Moves the internal pointer to the previous character and return it
     * (or empty string on failure).
     *
     * @return string
     */
    public function getPrevChar() : string
    {
        if ($this->idx >= 0) {
            --$this->idx;
            if ($this->idx >= 0) {
                $this->char = $this->str[$this->idx];
                if ($this->char === $this->newLineChar) {
                    --$this->line;
                    $this->column = $this->getColumnNumber($this->idx);
                } else {
                    --$this->column;
                }
                return $this->char;
            } else {
                $this->char = '';
                $this->column = 0;
            }
        }
        return $this->char;
    }
    
    /**
     * Remembers the current state of the iterator.
     *
     * @param string $key The state name.
     * @return void
     */
    public function remember(string $key)
    {
        $this->states[$key] = [
            $this->idx,
            $this->line,
            $this->column,
            $this->char
        ];
    }
    
    /**
     * Restores the previously saved state of the iterator.
     *
     * @param string $key The state name.
     * @param bool $removeState Determines whether to remove the stored state.
     * @return void
     * @throws \UnexpectedValueException
     */
    public function restore(string $key, $removeState = true)
    {
        if (isset($this->states[$key])) {
            list($this->idx, $this->line, $this->column, $this->char) = $this->states[$key];
            if ($removeState) {
                unset($this->states[$key]);
            }
        } else {
            throw new \UnexpectedValueException('The state with the given key does not exist.');
        }
    }

    /**
     * Clear all stored states.
     *
     * @return void
     */
    public function clear()
    {
        $this->states = [];
    }

    /**
     * Returns the column number by the character's index.
     *
     * @param int $idx
     * @return int
     */
    private function getColumnNumber(int $idx) : int
    {
        $pos = strrpos(substr($this->str, 0, $idx), $this->newLineChar);
        return $idx - ($pos === false ? -1 : $pos);
    }
}