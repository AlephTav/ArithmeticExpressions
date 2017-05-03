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
class Func implements IExpression
{
    /**
     * The function name.
     *
     * @var string
     */
    private $func = null;
    
    /**
     * The function arguments.
     *
     * @var array
     */
    private $args = [];
    
    /**
     * Constructor.
     *
     * @param string $func The function name.
     * @param \ArithmeticExpressions\AST\Interfaces\IExpression[] $args The function arguments.
     */
    public function __construct(string $func, array $args = [])
    {
        $this->func = $func;
        foreach ($args as $arg) {
            $this->add($arg);
        }
    }
    
    /**
     * Add a function argument.
     *
     * @param \ArithmeticExpressions\AST\Interfaces\IExpression $arg
     * @return void
     */
    public function add(IExpression $arg)
    {
        $this->args[] = $arg;
    }
    
    /**
     * Evaluates the function.
     *
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \ArithmeticExpressions\Exceptions\InterpreterException
     */
    public function evaluate()
    {
        $values = [];
        foreach ($this->args as $arg) {
            $values[] = $arg->evaluate();
        }
        switch ($this->func) {
            case 'log':
                $this->validate($values, 2, 2);
                if ($values[1] < 0) {
                    throw new \InvalidArgumentException('Log base must be greater than 0.');
                }
                return log($values[0], $values[1]);
            case 'lg':
                $this->validate($values, 1, 1);
                return log($values[0], 10);
            case 'ln':
                $this->validate($values, 1, 1);
                return log($values[0], M_E);
            case 'exp':
                $this->validate($values, 1, 1);
                return exp($values[0]);
            case 'sqrt':
                $this->validate($values, 1, 1);
                return sqrt($values[0]);
            case 'sin':
                $this->validate($values, 1, 1);
                return sin($values[0]);
            case 'cos':
                $this->validate($values, 1, 1);
                return cos($values[0]);
            case 'rand':
                $this->validate($values, 0, 0);
                return mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
        }
        throw new InterpreterException('Unknown function "' . $this->func . '".');
    }
    
    /**
     * Returns the string representation of the expression.
     *
     * @return string
     */
    public function toString() : string
    {
        $s = $this->func . '(';
        $args = [];
        foreach ($this->args as $arg) {
            $args[] = $arg->toString();
        }
        $s .= implode(', ', $args) . ')';
        return $s;
    }
    
    /**
     * Returns the string representation of the AST node.
     *
     * @return string
     */
    public function toAstString() : string
    {
        $s = $this->func . '(';
        $args = [];
        foreach ($this->args as $arg) {
            $args[] = $arg->toAstString();
        }
        $s .= implode(', ', $args) . ')';
        return $s;
    }
    
    /**
     * Validates function arguments.
     *
     * @param array $values The function arguments.
     * @param int $min The minimum number of arguments.
     * @param int $max The maximum number of arguments.
     * @return void
     * @throws \InterpreterException
     */
    private function validate(array $values, int $min, int $max)
    {
        $count = count($values);
        if ($count < $min) {
            throw new InterpreterException('Insufficient function arguments.');
        }
        if ($count > $max) {
            throw new InterpreterException('Too many arguments.');
        }
    }
}