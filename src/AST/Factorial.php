<?php
 
namespace ArithmeticExpressions\AST;

use ArithmeticExpressions\AST\Interfaces\IExpression;

/**
 * The implementation of the factorial operation.
 *
 * @author Sergey Milimko <smilimko@gmail.com>
 * @version 0.0.1
 */
class Factorial implements IExpression
{    
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
     * Evaluates the binary operation.
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function evaluate()
    {
        $op = (float)$this->operand->evaluate();
        if ($op < 0) {
            throw new \InvalidArgumentException('The factorial operand must be non-negative.');
        }
        if ($op > 170) {
            throw new \InvalidArgumentException('The factorial operand must be less than 171.');
        }
        if ($op != floor($op)) {
            throw new \InvalidArgumentException('The factorial operand must be integer.');
        }
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