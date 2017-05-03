<?php
 
namespace ArithmeticExpressions\AST;

use ArithmeticExpressions\AST\Interfaces\IExpression;

/**
 * The implementation of any real number.
 *
 * @author Sergey Milimko <smilimko@gmail.com>
 * @version 0.0.1
 */
class Number implements IExpression
{
    /**
     * The real number.
     */
    private $value = 0;
    
    /**
     * Constructor.
     *
     * @param float $value The real number.
     */
    public function __construct(float $value)
    {
        $this->value = $value;
    }
    
    /**
     * Returns the real number.
     *
     * @return float
     */
    public function evaluate()
    {
        return $this->value;
    }
    
    /**
     * Returns the string representation of the expression.
     *
     * @return string
     */
    public function toString() : string
    {
        return (string)$this->value;
    }
    
    /**
     * Returns the string representation of the AST node.
     *
     * @return string
     */
    public function toAstString() : string
    {
        return (string)$this->value;
    }
}