<?php

use PHPUnit\Framework\TestCase;
use ArithmeticExpressions\Exceptions\InterpreterException;
use ArithmeticExpressions\CharacterIterator;
use ArithmeticExpressions\Lexer;
use ArithmeticExpressions\Parser;
use ArithmeticExpressions\AST;

/**
 * Test cases for all AST classes.
 */
class InterpreterTest extends TestCase
{
    /**
     * The expression provider.
     *
     * @return array
     */
    public function expressionProvider()
    {
        return [
            ['2', 2],
            ['4.57', 4.57],
            ['-5', -5],
            ['+5.67', 5.67],
            ['!0', 1],
            ['!1', 0],
            ['~1', -2],
            ['~0', -1],
            ['0!', 1],
            ['5!', 120],
            ['1 + 2', 3],
            ['-.56 - 3.11', -3.67],
            ['3 * 4', 12],
            ['3 / 2', 1.5],
            ['4 | 3', 7],
            ['4 & 7', 4],
            ['4 ^ 7', 3],
            ['6 % 4', 2],
            ['1 || 0', true],
            ['1 && 0', false],
            ['2 ** 10', 1024],
            ['log(256, 16)', 2],
            ['lg(100)', 2],
            ['ln(2.71828182845904523536)', 1],
            ['exp(1)', 2.71828182845904523536],
            ['sqrt(16)', 4],
            ['sin(1.57079632679489661923)', 1],
            ['cos(1.57079632679489661923)', 0],
            ['2 * (3 + -5 ** 2 * 2) / 3! * 3', -47],
            ['(1 || 0) * (-6 / 2 ** 2 + (~5 ^ 6 | (2 && 1)))', -4.5],
            ['666.00029265438 | lg(0.819 - rand()) || 5.29E-41', true],
            ['-(~(924.00015051174 ^ (108 | 9000 / ~((1.2 + -(996 | 263 | ~(log(-(736 ^ 393), 805) ** 5 | 2.3)) & 334.005) - 48.57) / 9.1)) ^ 920.0895)', 122]
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
            ['(-1)!', AST\Factorial::ERR_FACTORIAL_1],
            ['171!', AST\Factorial::ERR_FACTORIAL_2],
            ['1.5!', AST\Factorial::ERR_FACTORIAL_3],
            ['log(1, 0)', AST\Func::ERR_FUNC_1],
            ['log(1)', AST\Func::ERR_FUNC_2],
            ['log(1, 2, 3)', AST\Func::ERR_FUNC_3],
            ['lg()', AST\Func::ERR_FUNC_2],
            ['lg(1, 2)', AST\Func::ERR_FUNC_3],
            ['ln()', AST\Func::ERR_FUNC_2],
            ['ln(1, 2)', AST\Func::ERR_FUNC_3],
            ['exp()', AST\Func::ERR_FUNC_2],
            ['exp(1, 2)', AST\Func::ERR_FUNC_3],
            ['sqrt()', AST\Func::ERR_FUNC_2],
            ['sqrt(1, 2)', AST\Func::ERR_FUNC_3],
            ['sin()', AST\Func::ERR_FUNC_2],
            ['sin(1, 2)', AST\Func::ERR_FUNC_3],
            ['cos()', AST\Func::ERR_FUNC_2],
            ['cos(1, 2)', AST\Func::ERR_FUNC_3],
            ['rand(1)', AST\Func::ERR_FUNC_3],
            ['1 / 0', AST\Binop::ERR_BINOP_1],
            ['4 % 0', AST\Binop::ERR_BINOP_2]
        ];
    }

    /**
     * The test for valid expressions.
     *
     * @dataProvider expressionProvider
     * @param string $expression The arithmetic expression.
     * @param string $value The expression value.
     */
    public function testInterpreter(string $expression, $value)
    {
        $parser = new Parser(new Lexer(new CharacterIterator($expression)));
        $this->assertEquals($value, $parser->parse()->evaluate());
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
            (new Parser(new Lexer(new CharacterIterator($expression))))->parse()->evaluate();
        } catch (InterpreterException $e) {
            $this->assertEquals($error, $e->getMessage());
        }
    }
}