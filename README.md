# Lexer, parser and interpreter for simple arithmetic expressions

The code is represented by examples of a lexer (based on [the finite-state machine](https://en.wikipedia.org/wiki/Finite-state_machine)), parser (based on [the shunting-yard algorithm](https://en.wikipedia.org/wiki/Shunting-yard_algorithm)) and interpreter (based on [the interpreter design pattern](https://en.wikipedia.org/wiki/Interpreter_pattern)). It can be used for educational purposes.

### The grammar of supported arithmetic expressions (in the extended Backus–Naur form)

```EBNF
expression = [ white space ] , expression , [ white space ] | "(" , expression , ")" | expression , binop , expression | unop , expression |  expression , "!" | function |  number ;
binop = "+" | "-" | "/" | "*" |  "%"  | "**" | "&&" | "||" | "^" | "&" | "|" ;
unop = "+" | "-" | "!" | "~" ;
function = function name , [ white space ] , "(" , function arguments , ")" ;
function name = "log" | "ln" | "lg" | "exp" | "sqrt" | "sin" | "cos" | "rand" ;
function arguments = [ expression ] , { "," , expression } ;
number = ( integer , [ "." ] , [ integer ] | "." , integer ) , [ ( "e" | "E" ) , [ ( "-" | "+" ) ] , integer ] ;
integer = digit , { digit } ;
digit = "0" | "1" | "2" | "3" | "4" | "5" | "6" | "7" | "8" | "9" ;
white space = ? any non-empty sequence of white space charachters ? ;
```

The associativity and precedence of supported operations:

Operator | Associativity | Precedence
---------|---------------|-----------
! (factorial) | left     | 10
**       | right         | 9
-(unary) +(unary) ~ | right | 8
!        | right         | 7
\* / %   | left          | 6
\- +     | left          | 5
&        | left          | 4
^        | left          | 3
\|       | left          | 2
&&       | left          | 1
\|\|     | left          | 0

### The basic usage

```php
$expression = '2 * (5 - 3) ** ((1 + -4) / (2 + 7))';
$lexer = new Lexer($expression);
$parser = new Parser($lexer);
$ast = $parser->parse();   // $ast is an abstract syntax tree (AST) of an expression
$value = $ast->evaluate(); // $value will contain 1.5874010519682
```

You can also try to evaluate different expressions using the script `examples/try.php`.
