<?php

namespace ArithmeticExpressions;

use ArithmeticExpressions\AST;
use ArithmeticExpressions\Interfaces\ILexer;
use ArithmeticExpressions\Exceptions\ParserException;

/**
 * Arithmetic expressions parser.
 *
 * @author Sergey Milimko <smilimko@gmail.com>
 * @version 0.0.1
 */
class Parser
{
    /**
     * Error message templates.
     */
    const ERR_PARSER_1 = 'Mismatched parentheses. Line %d, column %d.';
    const ERR_PARSER_2 = 'Unexpected "%s". Line %d, column %d.';
    const ERR_PARSER_3 = 'Invalid expression.';
    
    /**
     * The lexer of arithmetic expresssions.
     *
     * @var \ArithmeticExpressions\Interfaces\ILexer
     */
    private $lexer = null;
    
    /**
     * Precedence and associativity of the operators.
     * The value format: [precedence, associativity], 
     * where the associativity can take the following values:
     * 0 - left associative,
     * 1 - right associative,
     * -1 - non-associative.
     *
     * @var array
     */
    private static $operators = [
        '||' => [0, 0],
        '&&' => [1, 0],
        '|'  => [2, 0],
        '^'  => [3, 0],
        '&'  => [4, 0],
        '+'  => [5, 0],
        '-'  => [5, 0],
        '*'  => [6, 0],
        '/'  => [6, 0],
        '%'  => [6, 0],
        '!u' => [7, 1],
        '~u' => [8, 1],
        '+u' => [8, 1],
        '-u' => [8, 1],
        '**' => [9, 1]
    ];
    
    /**
     * Returns the operator precedence.
     *
     * @param string $operator
     * @param int $default The default priority.
     * @return int
     */
    public static function getOperatorPrecedence(string $operator, int $default = -1) : int
    {
        return self::$operators[$operator][0] ?? $default;
    }
    
    /**
     * Constructor.
     *
     * @param \ArithmeticExpressions\Interfaces\Lexer $lexer The lexer of arithmetic expresssions.
     */
    public function __construct(ILexer $lexer)
    {
        $this->lexer = $lexer;
    }
    
    /**
     * Parses the given (by lexer) token sequence of an arithmetic expresssion.
     *
     * @return \ArithmeticExpressions\AST\Interfaces\IExpression
     * @throws \ArithmeticExpressions\Exceptions\ParserException
     */
    public function parse() : AST\Interfaces\IExpression
    {
        $ops = new \SplStack();
        $arg = new \SplStack();
        $ast = new \SplStack();
        $prevToken = null;
        foreach ($this->lexer->getTokens() as $token) {
            switch ($token[0]) {
                case Lexer::T_WHITESPACE:
                    $token = $prevToken;
                    break;
                case Lexer::T_NUMBER:
                    if ($prevToken && 
                       ($prevToken[0] == Lexer::T_NUMBER || 
                        $prevToken[0] == Lexer::T_FACTORIAL || 
                        $prevToken[0] == Lexer::T_RIGHT_PARENTHESIS)) {
                        throw new ParserException(sprintf(self::ERR_PARSER_2, $token[1], $token[2], $token[3]));
                    }
                    $ast->push(new AST\Number($token[1]));
                    break;
                case Lexer::T_BINOP:
                    if ($prevToken && 
                       ($prevToken[0] == Lexer::T_COMMA ||
                        $prevToken[0] == Lexer::T_BINOP ||
                        $prevToken[0] == Lexer::T_UNOP ||
                        $prevToken[0] == Lexer::T_LEFT_PARENTHESIS)) {
                        throw new ParserException(sprintf(self::ERR_PARSER_2, $token[1], $token[2], $token[3]));
                    }
                    $op1 = $this->getNormalizedOperator($token);
                    while (false !== ($op2 = $this->getTopOperator($ops)) &&
                        (self::$operators[$op1][1] == 0 && self::$operators[$op1][0] <= self::$operators[$op2][0] ||
                         self::$operators[$op1][1] == 1 && self::$operators[$op1][0] < self::$operators[$op2][0])) {
                        $this->pushToAST($ops, $ast, $token);
                    }
                    $ops->push($token);
                    break;
                case Lexer::T_UNOP:
                    if ($prevToken && 
                       ($prevToken[0] == Lexer::T_NUMBER || 
                        $prevToken[0] == Lexer::T_RIGHT_PARENTHESIS || 
                        $prevToken[0] == Lexer::T_FACTORIAL)) {
                        throw new ParserException(sprintf(self::ERR_PARSER_2, $token[1], $token[2], $token[3]));
                    }
                    $ops->push($token);
                    break;
                case Lexer::T_LEFT_PARENTHESIS:
                    if ($prevToken && 
                       ($prevToken[0] == Lexer::T_NUMBER || 
                        $prevToken[0] == Lexer::T_RIGHT_PARENTHESIS)) {
                        throw new ParserException(sprintf(self::ERR_PARSER_2, $token[1], $token[2], $token[3]));
                    }
                    $ops->push($token);
                    break;
                case Lexer::T_RIGHT_PARENTHESIS:
                    if ($prevToken && $prevToken[0] == Lexer::T_COMMA) {
                        throw new ParserException(sprintf(self::ERR_PARSER_2, $token[1], $token[2], $token[3]));
                    }
                    while (!$ops->isEmpty() && (Lexer::T_LEFT_PARENTHESIS != $ops->top()[0])) {
                        $this->pushToAST($ops, $ast, $token);
                    }
                    if ($ops->isEmpty()) {
                        throw new ParserException(sprintf(self::ERR_PARSER_1, $token[2], $token[3]));
                    }
                    $ops->pop();
                    if (!$ops->isEmpty() && $ops->top()[0] == Lexer::T_FUNCTION) {
                        $args = [];
                        if (!$prevToken || $prevToken[0] != Lexer::T_LEFT_PARENTHESIS) {
                            $n = $arg->pop() + 1;
                            while ($n > 0 && !$ast->isEmpty()) {
                                $args[] = $ast->pop();
                                --$n;
                            }
                            $args = array_reverse($args);
                        }
                        $ast->push(new AST\Func($ops->pop()[1], $args));
                    }
                    break;
                case Lexer::T_FUNCTION:
                    $ops->push($token);
                    $arg->push(0);
                    break;
                case Lexer::T_COMMA:
                    if ($arg->isEmpty() || $prevToken && 
                       ($prevToken[0] == Lexer::T_COMMA || 
                        $prevToken[0] == Lexer::T_LEFT_PARENTHESIS)) {
                        throw new ParserException(sprintf(self::ERR_PARSER_2, $token[1], $token[2], $token[3]));
                    }
                    $arg->push($arg->pop() + 1);
                    while (!$ops->isEmpty() && $ops->top()[0] != Lexer::T_LEFT_PARENTHESIS) {
                        $this->pushToAST($ops, $ast, $token);
                    }
                    if ($ops->isEmpty()) {
                        throw new ParserException(sprintf(self::ERR_PARSER_2, $token[1], $token[2], $token[3]));
                    }
                    break;
                case Lexer::T_FACTORIAL:
                    if ($ast->isEmpty()) {
                        throw new ParserException(sprintf(self::ERR_PARSER_2, $token[1], $token[2], $token[3]));
                    }
                    $ast->push(new AST\Factorial($ast->pop()));
                    break;
            }
            $prevToken = $token;
        }
        while (false !== $this->getTopOperator($ops)) {
            $this->pushToAST($ops, $ast, $token);
        }
        if (!$ops->isEmpty()) {
            if ($ops->top()[0] == Lexer::T_LEFT_PARENTHESIS) {
                $token = $ops->top();
                throw new ParserException(sprintf(self::ERR_PARSER_1, $token[2], $token[3]));
            }
            throw new ParserException(self::ERR_PARSER_3);
        }
        if ($ast->count() != 1) {
            throw new ParserException(self::ERR_PARSER_3);
        }
        return $ast->pop();
    }
    
    /**
     * Pushes the operation to an AST.
     *
     * @param \SplStack $ops The operator stack.
     * @param \SplStack $ast The AST stack.
     * @param array $token The current token. 
     * @return void
     * @throws \ArithmeticExpressions\Exceptions\ParserException
     */
    private function pushToAST(\SplStack $ops, \SplStack $ast, array $token)
    {
        $op = $ops->pop();
        if ($op[0] == Lexer::T_BINOP) {
            if ($ast->count() < 2) {
                throw new ParserException(sprintf(self::ERR_PARSER_2, $token[1], $token[2], $token[3]));
            }
            $op2 = $ast->pop();
            $op1 = $ast->pop();
            $ast->push(new AST\Binop($op[1], $op1, $op2));
        } else {
            if ($ast->isEmpty()) {
                throw new ParserException(sprintf(self::ERR_PARSER_2, $token[1], $token[2], $token[3]));
            }
            $ast->push(new AST\Unop($op[1], $ast->pop()));
        }
    }
    
     /**
     * Returns an operator from the top of
     * the operator stack or FALSE on failure.
     * 
     * @param \SplStack $stack 
     * @return string|bool
     */
    private function getTopOperator(\SplStack $stack)
    {
        if (!$stack->isEmpty()) {
            $op = $this->getNormalizedOperator($stack->top());
            if (isset(self::$operators[$op])) {
                return $op;
            }
        }
        return false;
    }
    
    /**
     * Returns the normalized operator value.
     * This method adds "u" at the end of "+" or "-" operator if they are an unary operations.
     *
     * @param array $token
     * @return string
     */
    private function getNormalizedOperator(array $token) : string
    {
        $op = $token[1];
        return $op . ($token[0] == Lexer::T_UNOP ? 'u' : '');
    }
}    