<?php

use PHPUnit\Framework\TestCase;
use ArithmeticExpressions\CharacterIterator;

/**
 * Test cases for \ArithmeticExpressions\CharacterIterator class.
 */
class CharacterIteratorTest extends TestCase
{
    /**
     * The character sequences provider.
     *
     * @return array
     */
    public function textProvider()
    {
        return [
            ['a', "\n"],
            ['ab', "\n"],
            ['abc', "\n"],
            ["\n", "\n"],
            ["\n\n", "\n"],
            ["\n\n\n", "\n"],
            ["a\nbc\ndef\n", "\n"],
            ["a\n\nb\nc", "\n"],
            ["\na\n\n", "\n"],
            ['|a|bcd', '|'],
            ['||a||', '|']
        ];
    }

    /**
     * Test the iteration over an empty string.
     */
    public function testEmptyTextIteration()
    {
        $iterator = new CharacterIterator('');
        $this->assertEquals(0, count($iterator));
        $this->assertEquals('', $iterator->getChar());
        $this->assertEquals(false, $iterator->isValid());
        $this->assertEquals(null, $iterator->getIndex());
        $this->assertEquals(null, $iterator->getColumn());
        $this->assertEquals(null, $iterator->getLine());
        foreach ($iterator->getForwardIterator() as $char) {
            throw new \RuntimeException('Invalid character iterator.');
        }
        foreach ($iterator->getBackwardIterator() as $char) {
            throw new \RuntimeException('Invalid character iterator.');
        }
        $this->assertEquals(0, count($iterator));
        $this->assertEquals('', $iterator->getChar());
        $this->assertEquals(false, $iterator->isValid());
        $this->assertEquals(null, $iterator->getIndex());
        $this->assertEquals(null, $iterator->getColumn());
        $this->assertEquals(null, $iterator->getLine());
    }

    /**
     * Test the forward iteration.
     *
     * @covers CharacterIterator::getNextChar
     * @dataProvider textProvider
     * @param string $text
     * @param string $newLineChar
     */
    public function testForwardIterator(string $text, string $newLineChar)
    {
        $i = 0;
        $line = $column = 1;
        $iterator = new CharacterIterator($text, $newLineChar);
        foreach ($iterator->getForwardIterator() as $index => $char) {
            $ch = $text[$i];
            $this->assertEquals($i, $index);
            $this->assertEquals($ch, $char);
            $this->assertEquals($line, $iterator->getLine());
            $this->assertEquals($column, $iterator->getColumn());
            if ($ch == $newLineChar) {
                ++$line;
                $column = 1;
            } else {
                ++$column;
            }
            ++$i;
        }
    }

    /**
     * Test the backward iteration.
     *
     * @covers CharacterIterator::getPrevChar
     * @dataProvider textProvider
     * @param string $text
     * @param string $newLineChar
     */
    public function testBackwardIterator(string $text, string $newLineChar)
    {
        $i = strlen($text) - 1;
        $lines = explode($newLineChar, $text);
        $line = count($lines);
        $column = strlen($lines[$line - 1]) + 1;
        $iterator = new CharacterIterator($text, $newLineChar);
        foreach ($iterator->getBackwardIterator() as $index => $char) {
            $ch = $text[$i];
            if ($ch == $newLineChar) {
                --$line;
                $column = strlen($lines[$line - 1]) + 1;
            } else {
                --$column;
            }
            $this->assertEquals($i, $index);
            $this->assertEquals($ch, $char);
            $this->assertEquals($line, $iterator->getLine());
            $this->assertEquals($column, $iterator->getColumn());
            --$i;
        }
    }

    /**
     * Test the combination of forward and backward iterations.
     *
     * @covers CharacterIterator::getNextChar
     * @covers CharacterIterator::getPrevChar
     * @dataProvider textProvider
     * @param string $text
     * @param string $newLineChar
     */
    public function testForwardBackwardIteration(string $text, string $newLineChar)
    {
        $iterator = new CharacterIterator($text, $newLineChar);
        for ($i = 1; $i >= 0; --$i) {
            // Forward iterations.
            $i = 0;
            $line = $column = 1;
            while ('' !== $char = $iterator->getNextChar()) {
                $ch = $text[$i];
                $this->assertEquals($i, $iterator->getIndex());
                $this->assertEquals($ch, $char);
                $this->assertEquals($line, $iterator->getLine());
                $this->assertEquals($column, $iterator->getColumn());
                if ($ch == $newLineChar) {
                    ++$line;
                    $column = 1;
                } else {
                    ++$column;
                }
                ++$i;
            }
            // Backward iterations.
            $i = strlen($text) - 1;
            $lines = explode($newLineChar, $text);
            $line = count($lines);
            $column = strlen($lines[$line - 1]) + 1;
            while ('' !== $char = $iterator->getPrevChar()) {
                $ch = $text[$i];
                if ($ch == $newLineChar) {
                    --$line;
                    $column = strlen($lines[$line - 1]) + 1;
                } else {
                    --$column;
                }
                $this->assertEquals($i, $iterator->getIndex());
                $this->assertEquals($ch, $char);
                $this->assertEquals($line, $iterator->getLine());
                $this->assertEquals($column, $iterator->getColumn());
                --$i;
            }
        }
    }

    /**
     * Test the ability to save the iterator's states.
     *
     * @covers CharacterIterator::remember
     * @covers CharacterIterator::restore
     */
    public function testIteratorStates()
    {
        $text = '0\n123\n45\n6789';
        $length = strlen($text);
        $iterator = new CharacterIterator($text);
        for ($i = 5; $i >= 0; --$i) {
            $n = rand(1, $length);
            while ($n) {
                $iterator->getNextChar();
                --$n;
            }
            $state = [
                'char' => $iterator->getChar(),
                'index' => $iterator->getIndex(),
                'line' => $iterator->getLine(),
                'column' => $iterator->getColumn()
            ];
            $iterator->remember('key');
            $n = rand(1, $length);
            while ($n) {
                $iterator->getPrevChar();
                --$n;
            }
            $iterator->restore('key');
            $this->assertEquals($state, [
                'char' => $iterator->getChar(),
                'index' => $iterator->getIndex(),
                'line' => $iterator->getLine(),
                'column' => $iterator->getColumn()
            ]);
        }
    }

    /**
     * Test the restoring from a non-existing state.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testNonExistingState()
    {
        $iterator = new CharacterIterator('abc');
        $iterator->restore('key');
    }

    /**
     * Test the removing states.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testStateRemoving()
    {
        $iterator = new CharacterIterator('abc');
        $iterator->getNextChar();
        $iterator->remember('key1');
        $iterator->getPrevChar();
        $iterator->remember('key2');
        try {
            $iterator->restore('key1', false);
        } catch (\UnexpectedValueException $e) {
            throw new \LogicException('The state was removed, while it shouldn\'t.');
        }
        $iterator->clear();
        try {
            $iterator->restore('key1');
        } catch (\UnexpectedValueException $e) {
            $iterator->restore('key2');
        }
    }
}