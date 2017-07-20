<?php

use PHPUnit\Framework\TestCase;
use ArithmeticExpressions\Exceptions\ParserException;
use ArithmeticExpressions\CharacterIterator;
use ArithmeticExpressions\Lexer;
use ArithmeticExpressions\Parser;

/**
 * Test cases for \ArithmeticExpressions\Parser class.
 */
class ParserTest extends TestCase
{
    /**
     * The expression provider.
     *
     * @return array
     */
    public function expressionProvider()
    {
        return [
            ['0', '0'],
            ['123', '123'],
            ['123.333', '123.333'],
            ['45!', 'factorial(45)'],
            ['-5', 'unop("-", 5)'],
            ['+5', 'unop("+", 5)'],
            ['!0', 'unop("!", 0)'],
            ['~1', 'unop("~", 1)'],
            ['--6.45', 'unop("-", unop("-", 6.45))'],
            ['-+6.45', 'unop("-", unop("+", 6.45))'],
            ['~!6.45', 'unop("~", unop("!", 6.45))'],
            ['1 + 2', 'binop("+", 1, 2)'],
            ['2 * 5.11', 'binop("*", 2, 5.11)'],
            ['2 / 5.11', 'binop("/", 2, 5.11)'],
            ['2 ** 5.11', 'binop("**", 2, 5.11)'],
            ['2 && 5.11', 'binop("&&", 2, 5.11)'],
            ['2 || 5.11', 'binop("||", 2, 5.11)'],
            ['2 & 5.11', 'binop("&", 2, 5.11)'],
            ['2 | 5.11', 'binop("|", 2, 5.11)'],
            ['2 ^ 5.11', 'binop("^", 2, 5.11)'],
            ['2 % 5.11', 'binop("%", 2, 5.11)'],
            ['log(3.45, 16)', 'log(3.45, 16)'],
            ['lg(56.31)', 'lg(56.31)'],
            ['ln(2.71828)', 'ln(2.71828)'],
            ['exp(123)', 'exp(123)'],
            ['sqrt(45)', 'sqrt(45)'],
            ['sin(3.12)', 'sin(3.12)'],
            ['cos(7.45)', 'cos(7.45)'],
            ['rand()', 'rand()'],
            ['3.57 - -4.03', 'binop("-", 3.57, unop("-", 4.03))'],
            ['!4 + -3!', 'binop("+", unop("!", 4), unop("-", factorial(3)))'],
            ['2 * (3 + 4) * 3', 'binop("*", binop("*", 2, binop("+", 3, 4)), 3)'],
            ['(2 - !4) / (~5 + -6)', 'binop("/", binop("-", 2, unop("!", 4)), binop("+", unop("~", 5), unop("-", 6)))'],
            ['3 * 4 ** 5!', 'binop("*", 3, binop("**", 4, factorial(5)))'],
            ['1 * 2 ** 3 * 4', 'binop("*", binop("*", 1, binop("**", 2, 3)), 4)'],
            ['log(2 || ~(5 * 3) ** 2)!', 'factorial(log(binop("||", 2, unop("~", binop("**", binop("*", 5, 3), 2)))))'],
            [
                '(2 ** (2.11 / 3 + 5) * ~(3 + ln(6)))!',
                'factorial(binop("*", binop("**", 2, binop("+", binop("/", 2.11, 3), 5)), unop("~", binop("+", 3, ln(6)))))'
            ]
        ];
    }

    /**
     * The invalid expression provider.
     *
     * @return array
     */
    public function invalidExpressionProvider()
    {
        return [
            ["(1 +\n2))", sprintf(Parser::ERR_PARSER_1, 2, 3)],
            ['rand(', sprintf(Parser::ERR_PARSER_1, 1, 5)],
            ['1 2 3', sprintf(Parser::ERR_PARSER_2, '2', 1, 3)],
            ["1\n, 2, 3", sprintf(Parser::ERR_PARSER_2, ',', 2, 1)],
            ['2 ~ + 3', sprintf(Parser::ERR_PARSER_2, '~', 1, 3)],
            ['2 ( 5', sprintf(Parser::ERR_PARSER_2, '(', 1, 3)],
            ['2 + ** 5', sprintf(Parser::ERR_PARSER_2, '**', 1, 5)],
            ['2 ! 5', sprintf(Parser::ERR_PARSER_2, '5', 1, 5)],
            ['(1 + 2) ~ 5', sprintf(Parser::ERR_PARSER_2, '~', 1, 9)],
            ['1 ~ 5', sprintf(Parser::ERR_PARSER_2, '~', 1, 3)],
            ['1!!5', sprintf(Parser::ERR_PARSER_2, '5', 1, 4)],
            ['1(2', sprintf(Parser::ERR_PARSER_2, '(', 1, 2)],
            ['(1 + 2)(3 + 4)', sprintf(Parser::ERR_PARSER_2, '(', 1, 8)],
            ['log(1, 2,)', sprintf(Parser::ERR_PARSER_2, ')', 1, 10)],
            ['ln(,', sprintf(Parser::ERR_PARSER_2, ',', 1, 4)],
            ['1, 2', sprintf(Parser::ERR_PARSER_2, ',', 1, 2)],
            ['((1 + 2) * )', sprintf(Parser::ERR_PARSER_2, ')', 1, 12)],
            ['!', sprintf(Parser::ERR_PARSER_2, '!', 1, 1)],
            ['~', sprintf(Parser::ERR_PARSER_2, '~', 1, 1)],
            ['+', sprintf(Parser::ERR_PARSER_2, '+', 1, 1)],
            ['**', sprintf(Parser::ERR_PARSER_2, '**', 1, 1)],
            ['ln', Parser::ERR_PARSER_3],
            ['rand', Parser::ERR_PARSER_3],
            ['()', Parser::ERR_PARSER_3],
            ['', Parser::ERR_PARSER_3]
        ];
    }

    /**
     * The test for valid expressions.
     *
     * @dataProvider expressionProvider
     * @param string $expression The arithmetic expression.
     * @param string $ast The string representation of the expression's abstract syntax tree.
     */
    public function testParser(string $expression, string $ast)
    {
        $parser = new Parser(new Lexer(new CharacterIterator($expression)));
        $this->assertEquals($ast, $parser->parse()->toAstString());
    }

    /**
     * The test for invalid expressions.
     *
     * @dataProvider invalidExpressionProvider
     * @param string $expression The arithmetic expression.
     * @param string $error The error message.
     */
    public function testParserException(string $expression, string $error)
    {
        try {
            (new Parser(new Lexer(new CharacterIterator($expression))))->parse();
        } catch (ParserException $e) {
            $this->assertEquals($error, $e->getMessage());
        }
    }
}