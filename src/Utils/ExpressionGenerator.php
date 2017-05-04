<?php
 
namespace ArithmeticExpressions\Utils;

use ArithmeticExpressions\AST;
use ArithmeticExpressions\AST\Interfaces\IExpression;
use ArithmeticExpressions\Exceptions\LexerException;
use ArithmeticExpressions\Exceptions\ParserException;
use ArithmeticExpressions\Exceptions\InterpreterException;
use ArithmeticExpressions\Lexer;
use ArithmeticExpressions\Parser;

/**
 * You can use this class to generate random
 * arithmetic expression for testing.
 *
 * @author Sergey Milimko <smilimko@gmail.com>
 * @version 0.0.1
 */
class ExpressionGenerator
{
    /**
     * Binary operations.
     *
     * @var array
     */
    private $binops = [
        '+',
        '-',
        '*',
        '/',
        '%',
        '|',
        '&',
        '^',
        '**',
        '||',
        '&&'
    ];
    
    /**
     * Unary operations.
     *
     * @var array
     */
    private $unops = [
        '!',
        '+',
        '-',
        '~'
    ];
    
    /**
     * Function names.
     *
     * @var array
     */
    private $functions = [
        'log',
        'lg',
        'ln',
        'exp',
        'sin',
        'cos',
        'sqrt',
        'rand'
    ];
    
    /**
     * The AST of the generated expression.
     *
     * @var \ArithmeticExpressions\AST\Interfaces\IExpression
     */
    private $ast = null;
    
    /**
     * Returns the expression value.
     *
     * @return mixed
     */
    public function getExpressionValue()
    {
        return $this->ast ? $this->ast->evaluate() : null;
    }
    
    /**
     * Creates the random AST.
     *
     * @param int $minNumOps The minimum number of operations.
     * @param int $maxNumOps The maximum number of operations.
     * @return \ArithmeticExpressions\AST\Interfaces\IExpression     
     */
    public function createRandomAST(int $minNumOps = 5, int $maxNumOps = 20) : IExpression
    {
        $exp = $this->createRandomNumber();
        $n = (int)mt_rand($minNumOps, $maxNumOps);
        while ($n > 0) {
            if (mt_rand(0, 2) > 0) {
                $exp = $this->createRandomBinaryOperation($exp);
            } else {
                $exp = $this->createRandomUnaryOperation($exp);
            }
            --$n;
        }
        return $exp;
    }
    
    /**
     * Creates the random arithmetic expression.
     *
     * @param int $minNumOps The minimum number of operations.
     * @param int $maxNumOps The maximum number of operations.
     * @param float $errorProbability The probability of syntax error in the generated expression.
     * @return string
     */
    public function createRandomExpression(int $minNumOps = 5, int $maxNumOps = 20, float $errorProbability = 0) : string
    {
        $this->ast = $this->createRandomAST($minNumOps, $maxNumOps);
        $exp = $this->ast->toString();
        if (mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax() < 1 - $errorProbability) {
            return $exp;
        }
        do {
            $p = mt_rand(0, strlen($exp) - 1);
            switch (mt_rand(0, 5)) {
                case 0: // Invalid character.
                    $s = chr(mt_rand(ord('A'), ord('Z')));
                    break;
                case 1: // Unmatched parenthesis.
                    $s = mt_rand(0, 1) > 0 ? '(' : ')';
                    break;
                case 2: // Unexpected binary operation.
                    $s = $this->binops[mt_rand(2, count($this->binops) - 1)];
                    break;
                case 3: // Unexpected unary operation.
                    $s = $this->unops[mt_rand(1, count($this->unops) - 1)];
                    break;
                case 4: // Unexpected number.
                    $s = ' ' . mt_rand(1, 10) . ' ';
                    break;
                default: // Unexpected comma.
                    $s = ',';
                    break;
            }
            $exp = substr($exp, 0, $p) . $s . substr($exp, $p);
            $flag = true;
            try {
                (new Parser(new Lexer($exp)))->parse()->evaluate();
            } catch (LexerException $e) {
                $flag = false;
            } catch (ParserException $e) {
                $flag = false;
            } catch (InterpreterException $e) {
                $flag = false;
            } catch (\Throwable $e) {}
        }
        while ($flag);
        return $exp;
    }
    
    /**
     * Creates a random binary operation.
     *
     * @param \ArithmeticExpressions\AST\Interfaces\IExpression $operand
     * @return \ArithmeticExpressions\AST\Binop
     */
    private function createRandomBinaryOperation(IExpression $operand) : AST\Binop
    {
        $count = count($this->binops);
        $n = mt_rand(0, $count - 1);
        if (mt_rand(0, 1) > 0) {
            return new AST\Binop($this->binops[$n], $operand, $this->createRandomNumber());
        }
        return new AST\Binop($this->binops[$n], $this->createRandomNumber(), $operand);
    }
    
    /**
     * Creates a random unary operation (including function).
     *
     * @param \ArithmeticExpressions\AST\Interfaces\IExpression $operand
     * @return \ArithmeticExpressions\AST\Interfaces\IExpression
     */
    private function createRandomUnaryOperation(IExpression $operand) : IExpression
    {
        if (mt_rand(0, 20) == 0) {
            return new AST\Factorial($operand);
        }
        $count = count($this->unops);
        $n = mt_rand(0, 2 * $count);
        if ($n > 0 && $n < $count) {
            return new AST\Unop($this->unops[$n], $operand);
        }
        $name = $this->functions[mt_rand(0, count($this->functions) - 1)];
        $func = new AST\Func($name);
        if ($name == 'rand') {
            return $this->createRandomBinaryOperation($func);
        }
        $func->add($operand);
        if ($name == 'log') {
            $func->add(new AST\Number(mt_rand(0, 1000)));
        }
        return $func;
    }
    
    /**
     * Creates a random value (integer or float).
     *
     * @return \ArithmeticExpressions\AST\Number
     */
    private function createRandomNumber() : AST\Number {
        $num = mt_rand(0, 1000);
        if ($num > 0) {
            if (mt_rand(0, 1) > 0) {
                $num += 1 / mt_rand(2, 10000);
            }
            if (mt_rand(0, 2) > 1) {
                $num .= 'e' . (50 - mt_rand(0, 100));
            }
        }
        return new AST\Number($num);
    }
}