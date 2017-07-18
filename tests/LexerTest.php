<?php

use PHPUnit\Framework\TestCase;
use ArithmeticExpressions\Lexer;
use ArithmeticExpressions\CharacterIterator;

/**
 * Test cases for \ArithmeticExpressions\Lexer class.
 */
class LexerTest extends TestCase
{
    /**
     * @covers Lexer::getTokens
     */
    public function testEmptyExpression()
    {
        $lexer = new Lexer(new CharacterIterator(''));
        $tokens = $lexer->getTokens();
        $this->assertInstanceOf(\Generator::class, $tokens);
        $this->assertEmpty(iterator_to_array($tokens));
    }
}