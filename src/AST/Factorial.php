<?php
 
namespace ArithmeticExpressions\AST;

use ArithmeticExpressions\AST\Interfaces\IExpression;
use ArithmeticExpressions\Exceptions\InterpreterException;

/**
 * The implementation of the factorial operation.
 *
 * @author Sergey Milimko <smilimko@gmail.com>
 * @version 0.0.1
 */
class Factorial implements IExpression
{
    /**
     * The error message templates.
     */
    const ERR_FACTORIAL_1 = 'The factorial operand must be non-negative.';
    const ERR_FACTORIAL_2 = 'The factorial operand must be less than 171.';
    const ERR_FACTORIAL_3 = 'The factorial operand must be integer.';

    /**
     * The operand.
     *
     * @var \ArithmeticExpressions\AST\Interfaces\IExpression
     */
    private $operand = null;
    
    /**
     * Constructor.
     *
     * @param \ArithmeticExpressions\AST\Interfaces\IExpression $operand The operand.
     */
    public function __construct(IExpression $operand)
    {
        $this->operand = $operand;
    }
    
    /**
     * Evaluates the factorial.
     *
     * @return int
     * @throws \ArithmeticExpressions\Exceptions\InterpreterException
     */
    public function evaluate()
    {
        $op = (float)$this->operand->evaluate();
        if ($op < 0) {
            throw new InterpreterException(self::ERR_FACTORIAL_1);
        }
        if ($op > 170) {
            throw new InterpreterException(self::ERR_FACTORIAL_2);
        }
        if ($op != floor($op)) {
            throw new InterpreterException(self::ERR_FACTORIAL_3);
        }
        $op = (int)$op;
        $value = 1;
        while ($op >= 2) {
            $value *= $op;
            --$op;
        }
        return $value;
    }
    
    /**
     * Returns the string representation of the expression.
     *
     * @return string
     */
    public function toString() : string
    {
        $op = $this->operand->toString();
        if ($this->operand instanceof Number || $this->operand instanceof Func) {
            return $op . '!';
        }
        return '(' . $op . ')!';
    }
    
    /**
     * Returns the string representation of the AST node.
     *
     * @return string
     */
    public function toAstString() : string
    {
        return 'factorial(' . $this->operand->toAstString() . ')';
    }
}