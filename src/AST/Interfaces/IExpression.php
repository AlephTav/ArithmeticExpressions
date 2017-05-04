<?php

namespace ArithmeticExpressions\AST\Interfaces;

/**
 * The main interface for an implementation of arithmetic expressions.
 *
 * @author Sergey Milimko <smilimko@gmail.com>
 * @version 0.0.1
 */
interface IExpression
{
    /**
     * Evaluates the arithmetic expression.
     *
     * @return mixed
     */
    public function evaluate();
    
    /**
     * Returns the string representation of the expression.
     *
     * @return string
     */
    public function toString() : string;

    /**
     * Returns the string representation of the AST node.
     *
     * @return string
     */
    public function toAstString() : string;
}