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
     * The current column.
     *
     * @var int
     */
    private $column = 0;

    /**
     * The current line.
     */     
    private $line = 0;
    
    /**
     * The new line character.
     *
     * @var string
     */
    private $newLineChar = "\n";

    /**
     * The previously read char.
     *
     * @var string
     */     
    private $prevChar = '';
    
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
        $this->prevChar = $this->newLineChar = $newLineChar;
    }
    
    /**
     * Returns the number of characters.
     *
     * @return int
     */
    public function count() : int
    {
        return strlen($this->str);
    }
    
    /**
     * Returns the current line number.
     *
     * @return int
     */
    public function getLine() : int
    {
        return $this->line;
    }
    
    /**
     * Returns the current column number.
     *
     * @return int
     */
    public function getColumn() : int
    {
        return $this->column;
    }

    /**
     * Rewinds the iterator.
     *
     * @return void
     */
    public function rewind()
    {
        $this->idx = -1;
        $this->line = 0;
        $this->column = 0;
        $this->prevChar = $this->newLineChar;
        $this->states = [];
    }

    /**
     * Returns the current character or empty string on failure.
     *
     * @return string
     */
    public function current()
    {
        return $this->str[$this->idx] ?? '';
    }

    /**
     * Returns the position of the current character.
     *
     * @return int
     */
    public function key()
    {
        return $this->idx;
    }

    /**
     * Moves the internal pointer to the next character.
     *
     * @return void
     */
    public function next()
    {
        ++$this->idx;
    }

    /**
     * Returns TRUE if the internal pointer within the character 
     * sequence and FALSE otherwise.
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->str[$this->idx]);
    }
    
    /**
     * Returns the next character or empty string on failure.
     *
     * @return string
     */
    public function getNextChar() : string
    {
        ++$this->idx;
        if ($this->valid()) {
            if ($this->prevChar === $this->newLineChar) {
                $this->column = 1;
                ++$this->line;
            } else {
                ++$this->column;
            }
            return $this->prevChar = $this->str[$this->idx];
        }
        return '';
    }
    
    /**
     * Returns the previous character or empty string on failure.
     *
     * @return string
     */
    public function getPrevChar() : string
    {
        --$this->idx;
        if ($this->valid()) {
            $this->prevChar = $this->str[$this->idx];
            if ($this->prevChar === $this->newLineChar) {
                $this->column = $this->idx - (int)strrpos($this->str, $this->newLineChar, -$this->idx);
                --$this->line;
            } else {
                --$this->column;
            }
            return $this->prevChar;
        }
        return '';
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
            $this->prevChar
        ];
    }
    
    /**
     * Restores the previously saved state of the iterator.
     *
     * @param string $key The state name.
     * @return void
     */
    public function restore(string $key)
    {
        if ($this->states[$key]) {
            list($this->idx, $this->line, $this->column, $this->prevChar) = $this->states[$key];
            unset($this->states[$key]);
        }
    }
}