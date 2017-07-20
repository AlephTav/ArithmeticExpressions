<?php
 
namespace ArithmeticExpressions\AST;

use ArithmeticExpressions\Parser;
use ArithmeticExpressions\AST\Interfaces\IExpression;
use ArithmeticExpressions\Exceptions\InterpreterException;

/**
 * The implementation of any binary operation.
 *
 * @author Sergey Milimko <smilimko@gmail.com>
 * @version 0.0.1
 */
class Binop implements IExpression
{
    /**
     * The error message templates.
     */
    const ERR_BINOP_1 = 'Division by zero.';
    const ERR_BINOP_2 = 'Modulo by zero.';
    const ERR_BINOP_3 = 'Unknown binary operator "%s".';

    /**
     * The binary operator.
     *
     * @var string
     */
    private $operator = '';
    
    /**
     * The first operand.
     *
     * @var \ArithmeticExpressions\AST\Interfaces\IExpression
     */
    private $operand1 = null;
    
    /**
     * The second operand.
     *
     * @var \ArithmeticExpressions\AST\Interfaces\IExpression
     */
    private $operand2 = null;
    
    /**
     * Constructor.
     *
     * @param string $operator The binary operator.
     * @param \ArithmeticExpressions\AST\Interfaces\IExpression $operand1 The first operand.
     * @param \ArithmeticExpressions\AST\Interfaces\IExpression $operand2 The second operand.
     */
    public function __construct(string $operator, IExpression $operand1, IExpression $operand2)
    {
        $this->operator = $operator;
        $this->operand1 = $operand1;
        $this->operand2 = $operand2;
    }
    
    /**
     * Returns the operator precedence.
     *
     * @return int
     */
    public function getPrecedence() : int
    {
        return Parser::getOperatorPrecedence($this->operator); 
    }
    
    /**
     * Evaluates the binary operation.
     *
     * @return mixed
     * @throws \ArithmeticExpressions\Exceptions\InterpreterException
     * @throws \DivisionByZeroError
     */
    public function evaluate()
    {
        $op1 = $this->operand1->evaluate();
        $op2 = $this->operand2->evaluate();
        switch ($this->operator) {
            case '+':
                return $op1 + $op2;
            case '-';
                return $op1 - $op2;
            case '*':
                return $op1 * $op2;
            case '/':
                if ($op2 == 0) {
                    throw new InterpreterException(self::ERR_BINOP_1);
                }
                return $op1 / $op2;
            case '%':
                if ($op2 < 1) {
                    throw new InterpreterException(self::ERR_BINOP_2);
                }
                return $op1 % $op2;
            case '**':
                return $op1 ** $op2;
            case '&&':
                return $op1 && $op2;
            case '||':
                return $op1 || $op2;
            case '&':
                return $op1 & $op2;
            case '|':
                return $op1 | $op2;
            case '^':
                return $op1 ^ $op2;
        }
        throw new InterpreterException(sprintf(self::ERR_BINOP_3, $this->operator));
    }
    
    /**
     * Returns the string representation of the expression.
     *
     * @return string
     */
    public function toString() : string
    {
        $op1 = $this->operand1->toString();
        $op2 = $this->operand2->toString();
        if (($this->operand1 instanceof Binop || $this->operand1 instanceof Unop) && 
            $this->operand1->getPrecedence() < $this->getPrecedence()) {
            $op1 = '(' . $op1 . ')';
        }
        if (($this->operand2 instanceof Binop || $this->operand2 instanceof Unop) &&
            $this->operand2->getPrecedence() < $this->getPrecedence()) {
            $op2 = '(' . $op2 . ')';
        }
        return $op1 . ' ' . $this->operator . ' ' . $op2;
    }
    
    /**
     * Returns the string representation of the AST node.
     *
     * @return string
     */
    public function toAstString() : string
    {
        return 'binop("' . $this->operator . '", ' . 
            $this->operand1->toAstString() . ', ' .
            $this->operand2->toAstString() . ')';
    }
}