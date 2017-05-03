<?php
 
namespace ArithmeticExpressions\AST;

use ArithmeticExpressions\Parser;
use ArithmeticExpressions\AST\Interfaces\IExpression;
use ArithmeticExpressions\Exceptions\InterpreterException;

/**
 * The implementation of any unary operation.
 *
 * @author Sergey Milimko <smilimko@gmail.com>
 * @version 0.0.1
 */
class Unop implements IExpression
{
    /**
     * The binary operator.
     *
     * @var string
     */
    private $operator = '';
    
    /**
     * The operand.
     *
     * @var \ArithmeticExpressions\AST\Interfaces\IExpression
     */
    private $operand = null;
    
    /**
     * Constructor.
     *
     * @param string $operator The binary operator.
     * @param \ArithmeticExpressions\AST\Interfaces\IExpression $operand The operand.
     */
    public function __construct(string $operator, IExpression $operand)
    {
        $this->operator = $operator;
        $this->operand = $operand;
    }
    
    /**
     * Returns the operator precedence.
     *
     * @return int
     */
    public function getPrecedence() : int
    {
        return Parser::getOperatorPrecedence($this->operator . 'u'); 
    }
    
    /**
     * Evaluates the binary operation.
     *
     * @return mixed
     * @throws \ArithmeticExpressions\Exceptions\InterpreterException
     */
    public function evaluate()
    {
        $op = $this->operand->evaluate();
        switch ($this->operator) {
            case '+':
                return $op;
            case '-';
                return -$op;
            case '!':
                return !$op;
            case '~':
                return ~(float)$op;
        }
        throw new InterpreterException('Unknown unary operator "' . $this->operator . '".');
    }
    
    /**
     * Returns the string representation of the expression.
     *
     * @return string
     */
    public function toString() : string
    {
        $op = $this->operand->toString();
        $operator = $this->operator;
        if ($operator == '+') {
            return $op;
        }
        if (($this->operand instanceof Binop || $this->operand instanceof Unop) && 
            $this->operand->getPrecedence() < $this->getPrecedence()) {
            $op = '(' . $op . ')';
        }
        return $operator . $op;
    }
    
    /**
     * Returns the string representation of the AST node.
     *
     * @return string
     */
    public function toAstString() : string
    {
        return 'unop("' . $this->operator . '", ' . $this->operand->toAstString() . ')';
    }
}