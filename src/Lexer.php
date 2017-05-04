<?php

namespace ArithmeticExpressions;

use ArithmeticExpressions\Interfaces\ILexer;
use ArithmeticExpressions\Interfaces\ICharacterIterator;
use ArithmeticExpressions\Exceptions\LexerException;

/**
 * Lexical analyser for arithmetic expressions.
 *
 * @author Sergey Milimko <smilimko@gmail.com>
 * @version 0.0.1
 */
class Lexer implements ILexer
{
    /**
     * Error message templates.
     */
    const ERR_LEXER_1 = 'Invalid character. Line %d, column %d.';
    const ERR_LEXER_2 = 'Invalid token. Line %d, column %d.';
    
    /**
     * The tokens.
     */
    const T_WHITESPACE = 1;
    const T_NUMBER = 2;
    const T_BINOP = 3;
    const T_UNOP = 4;
    const T_FUNCTION = 5;
    const T_LEFT_PARENTHESIS = 6;
    const T_RIGHT_PARENTHESIS = 7;
    const T_COMMA = 8;
    const T_FACTORIAL = 9;

    /**
     * An instance of iterator over all characters of the input string.
     *
     * @var \ArithmeticExpressions\Interfaces\ICharacterIterator
     */
    private $chars = null;
    
    /**
     * This array is used to get character's symbol class.
     *
     * @var string[]
     */
    private $sc = [
        ' ' => ' ',
        "\t" => ' ',
        "\r" => ' ',
        "\n" => ' ',
        '0' => '9',
        '1' => '9',
        '2' => '9',
        '3' => '9',
        '4' => '9',
        '5' => '9',
        '6' => '9',
        '7' => '9',
        '8' => '9',
        '9' => '9',
        'a' => 'az',
        'b' => 'az',
        'c' => 'az',
        'd' => 'az',
        'e' => 'az',
        'f' => 'az',
        'g' => 'az',
        'h' => 'az',
        'i' => 'az',
        'j' => 'az',
        'k' => 'az',
        'l' => 'az',
        'm' => 'az',
        'n' => 'az',
        'o' => 'az',
        'p' => 'az',
        'q' => 'az',
        'r' => 'az',
        's' => 'az',
        't' => 'az',
        'u' => 'az',
        'v' => 'az',
        'w' => 'az',
        'x' => 'az',
        'y' => 'az',
        'z' => 'az'
    ];
    
    /**
     * The default transitions table for symbol class.
     * If $tt[$state][$symbol] does not exists, $dt[$symbol] is used.
     *
     * @var array
     */
    private $dt = [
        '' => -1,
        ' ' => -1,
        '(' => -5,
        ')' => -6,
        ',' => -8,
        '9' => -10,
        '.' => -12,
        '!' => -20,
        '~' => -21,
        '+' => -22,
        '-' => -23,
        '*' => -32,
        '/' => -33,
        '%' => -34,
        '^' => -35,
        '&' => -36,
        '|' => -37,
        'az' => -50
    ];
    
    /**
     * The state transition table.
     *
     * @var array
     */
    private $tt = [
        0 => [' ' => 1, '9' => 10, '.' => 12, '!' => 20, '~' => 21, '+' => 22, '-' => 23, '*' => 32, '/' => 33, '%' => 34, '^' => 35, '&' => 36, '|' => 37, '(' => 5, ')' => 6, ',' => 8, 'az' => 50],
        // Whitespace
        1 => [' ' => 1, '+' => -30, '-' => -31],
        // (
        5 => [],
        // )
        6 => ['+' => -30, '-' => -31, '!' => -18],
        // ,
        8 => [],
        // Number
        10 => ['9' => 10, '.' => 11, 'e' => 13, 'E' => 13, '!' => -18, '+' => -30, '-' => -31],
        11 => ['9' => 11, 'e' => 13, 'E' => 13, '!' => -18, '+' => -30, '-' => -31],
        12 => ['9' => 11, ' ' => false, '.' => false, '!' => false, '~' => false, '+' => false, '-' => false, '/' => false, '*' => false, '&' => false, '|' => false, '%' => false, '(' => false, ')' => false, ',' => false, 'az' => false],
        13 => ['-' => 15, '+' => 15, '9' => 14, '.' => false, '!' => false, '~' => false, '/' => false, '*' => false, '&' => false, '|' => false, '%' => false, '(' => false, ')' => false, ',' => false, 'az' => false],
        14 => ['9' => 14, '!' => -18, '+' => -30, '-' => -31],
        15 => ['9' => 14, ' ' => false, '.' => false, '!' => false, '~' => false, '+' => false, '-' => false, '/' => false, '*' => false, '&' => false, '|' => false, '%' => false, '(' => false, ')' => false, ',' => false, 'az' => false],
        // ! (factorial)
        18 => ['+' => -30, '-' => -31, '!' => -18],
        // ! (unary)
        20 => [],
        // ~ (unary)
        21 => [],
        // + (unary)
        22 => [],
        // - (unary)
        23 => [],
        // +
        30 => [],
        // -
        31 => [],
        // *
        32 => ['*' => 40],
        // /
        33 => [],
        // %
        34 => [],
        // ^
        35 => [],
        // &
        36 => ['&' => 41],
        // |
        37 => ['|' => 42],
        // **
        40 => [],
        // &&
        41 => [],
        // ||
        42 => [],
        // function
        50 => ['az' => 50, '9' => 50, '_' => 50]
    ];
    
    /**
     * This table defines an action $actions[$state1][$state2]
     * that should be performed for transition from $state1 to $state2.
     */
    private $actions = [
        1 => [20 => 20, 30 => 30, 31 => 30],
        50 => [1 => 50, 5 => 50, 6 => 50, 8 => 50, 12 => 50, 20 => 50, 21 => 50, 22 => 50, 23 => 50, 32 => 50, 33 => 50, 34 => 50, 35 => 50, 36 => 50, 37 => 50]
    ];
    
    /**
     * This array used to determine token by the current state of the FSA.
     *
     * @var int[]
     */
    private $tokens = [
        1 => self::T_WHITESPACE,
        5 => self::T_LEFT_PARENTHESIS,
        6 => self::T_RIGHT_PARENTHESIS,
        8 => self::T_COMMA,
        10 => self::T_NUMBER,
        11 => self::T_NUMBER,
        14 => self::T_NUMBER,
        18 => self::T_FACTORIAL,
        20 => self::T_UNOP,
        21 => self::T_UNOP,
        22 => self::T_UNOP,
        23 => self::T_UNOP,
        30 => self::T_BINOP,
        31 => self::T_BINOP,
        32 => self::T_BINOP,
        33 => self::T_BINOP,
        34 => self::T_BINOP,
        35 => self::T_BINOP,
        36 => self::T_BINOP,
        37 => self::T_BINOP,
        40 => self::T_BINOP,
        41 => self::T_BINOP,
        42 => self::T_BINOP,
        51 => self::T_FUNCTION
    ];
    
    /**
     * The valid function names.
     */
    private $functions = [
        'log' => 1,
        'ln' => 1,
        'lg' => 1,
        'exp' => 1,
        'sqrt' => 1,
        'sin' => 1,
        'cos' => 1,
        'rand' => 1
    ];
    
    /**
     * Converts the token array to a string representation.
     *
     * @param array $tokens
     * @return string
     */
    public static function toString(array $tokens) : string
    {
        $exp = '';
        foreach ($tokens as $token) {
            $exp .= $token[1];
        }
        return $exp;
    }
    
    /**
     * Constructor.
     *
     * @param \ArithmeticExpressions\Interfaces\ICharacterIterator $charIterator
     */
    public function __construct(ICharacterIterator $charIterator)
    {
        $this->chars = $charIterator;
    }
    
    /**
     * Returns iterator over all tokens.
     * Each token represents a numeric array of four elements:
     * [token number, token value, line, column]
     *
     * @return \Generator
     * @throws \ArithmeticExpressions\Exceptions\LexerException
     */
    public function getTokens() : \Generator
    {
        if (!$this->chars->count()) {
            return;
        }
        $word = '';
        $state = $prevState = $prevToken = 0;
        $tokenLine = $tokenColumn = 1;
        $this->chars->rewind();
        // Iterate over all characters.
        do {
            $char = $this->chars->getNextChar();
            // Move to a new state.
            $transition = $this->tt[$state];
            if (isset($this->sc[$char])) {
                $symbol = $this->sc[$char];
                $state = $transition[$symbol] ?? 
                    ($transition[$char] ?? ($this->dt[$symbol] ?? ($this->dt[$char] ?? false)));
            } else {
                $state = $transition[$char] ?? ($this->dt[$char] ?? false);
            }
            // Perform an action if any.
            if (isset($this->actions[$prevState][abs($state)])) {
                $action = 'action' . $this->actions[$prevState][abs($state)];
                $state = $this->{$action}($state, $prevToken, $prevState, $word, $char);
            }
            // Throw an exception if the current state is not determined.
            if ($state === false) {
                throw new LexerException(sprintf(self::ERR_LEXER_1, $this->chars->getLine(), $this->chars->getColumn()));
            }
            // If a token is formed, return it.
            if ($state < 0) {
                yield $this->formToken($word, $prevState, $tokenLine, $tokenColumn);
                $prevToken = $this->tokens[$prevState] != self::T_WHITESPACE ? $this->tokens[$prevState] : $prevToken;
                $tokenLine = $this->chars->getLine();
                $tokenColumn = $this->chars->getColumn();
                $state = -$state;
                $word = '';
            }
            // Add the current character to a token value.
            $word .= $char;
            $prevState = $state;
        } while ($char !== '');
    }
    
    /**
     * This action is invoked during transition from whitespace token to "!" token.
     * The method changes the current state to factorial operation if the previous token is number, factorial or ")".
     *
     * @param int $state The current state.
     * @param int $prevToken The previous token.
     * @param int $prevState The previous state.
     * @param string $word The current token value.
     * @param string $char The current input character.
     * @return int
     */
    private function action20(int $state, int $prevToken, &$prevState, &$word, &$char) : int
    {
        if ($prevToken == self::T_NUMBER || $prevToken == self::T_FACTORIAL ||
            $prevToken == self::T_RIGHT_PARENTHESIS) {
            return -18;
        }
        return $state;
    }
    
    /**
     * This action is invoked during transition from whitespace token to "+" or "-" tokens.
     * The method changes the current state to unary operation if the previous token is not number, factorial or ")".
     *
     * @param int $state The current state.
     * @param int $prevToken The previous token.
     * @param int $prevState The previous state.
     * @param string $word The current token value.
     * @param string $char The current input character.
     * @return int
     */
    private function action30(int $state, int $prevToken, &$prevState, &$word, &$char) : int
    {
        if ($prevToken != self::T_NUMBER && $prevToken != self::T_RIGHT_PARENTHESIS &&
            $prevToken != self::T_FACTORIAL) {
            return 8 - abs($state);
        }
        return $state;
    }
    
    /**
     * This action is invoked during transition from "function" token to other token.
     * The method changes the previous state if the token corresponds the known function names.
     *
     * @param int $state The current state.
     * @param int $prevToken The previous token.
     * @param int $prevState The previous state.
     * @param string $word The current token value.
     * @param string $char The current input character.
     * @return int
     */
    private function action50(int $state, int $prevToken, &$prevState, &$word, &$char) : int
    {
        $prevState = isset($this->functions[$word]) ? 51 : 50;
        return $state;
    }
    
    /**
     * Create an array with token info.
     *
     * @param string $word The token value.
     * @param int $state The state at the end of the token formation.
     * @param int $line The line number corresponds the first character of the token.
     * @param int $column The column number corresponds the first character of the token.
     * @return array 
     * @throws \ArithmeticExpressions\Exceptions\LexerException
     */
    private function formToken(string $word, int $state, int $line, int $column) : array
    {
        if (!isset($this->tokens[$state])) {
            throw new LexerException(sprintf(self::ERR_LEXER_2, $line, $column));
        }
        return [
            $this->tokens[$state],
            $word,
            $line,
            $column
        ];
    }
}