<?php

namespace ArithmeticExpressions;

use ArithmeticExpressions\Exceptions\LexerException;
use ArithmeticExpressions\Exceptions\ParserException;
use ArithmeticExpressions\Exceptions\InterpreterException;
use ArithmeticExpressions\Utils\ExpressionGenerator;

error_reporting(E_ALL);

// Bootstrap
spl_autoload_register(function($class)
{
    $path = __DIR__ . '/../src' . substr($class, strlen('ArithmeticExpressions')) . '.php';
    require_once($path);
});

$output = '';

// Process request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    
    // Generate expression.
    if ($_POST['action'] == 1) {
        $_POST['expression'] = (new ExpressionGenerator())->createRandomExpression(0, 20);
    }
    
    // Evaluation.
    try {
        $expression = $_POST['expression'] ?? '';
        $parser = new Parser(new Lexer($expression));
        $ast = $parser->parse();
        $value = $ast->evaluate();
        $output = '<b>AST:</b> ' . $ast->toAstString() . PHP_EOL . PHP_EOL;
        $output .= '<b>Expression:</b> ' . $ast->toString() . PHP_EOL . PHP_EOL;
        $output .= '<b>Value:</b> ' . (is_bool($value) ? ($value ? 'true' : 'false') : $value);
    } catch (\Throwable $e) {
        $output = get_class($e) . ': ' . $e->getMessage() . ' File: ' . $e->getFile() . '. Line: ' . $e->getLine() . '.';
    }
    
} else {
    $expression = (new ExpressionGenerator())->createRandomExpression(0, 20);
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Expression Interpreter</title>
<style type="text/css">
html, button, input, select, textarea {
    color: #222;
    font-family: sans-serif;
}
html {
    font-size: 100%;
}
body {
    color: #3e4349;
    font: 36px/140% Georgia,serif;
    margin: 0 auto;
    width: 800px;
}
h1 {
    color: #bc1864;
    font: italic 44px Georgia,serif;
    margin-left: auto;
    margin-right: auto;
    text-align: center;
    margin:0;
}
h2 {
    color: #3e4349;
    font: bold 34px Courier,monospace;
    text-align: center;
    margin:0;
}
h3 {
    color: #3e4349;
    font: bold 24px Courier,monospace;
    text-align: center;
    margin:0;
}
pre {
    font: 16px Courier,monospace;
    white-space: pre-wrap;
    word-wrap: break-word;
}
form {
    width: 100%;
    margin: 0;
}
textarea {
    font: 20px Courier,monospace;
    width: 100%;
    overflow: auto;
    resize: vertical;
    vertical-align: top;
}

input[type="text"] {
    font: 20px courier,monospace;
    width: 73%;
    margin: 0;
    vertical-align: baseline;
    color: #222;
}

input[type="submit"],input[type="button"] {
    font: 20px Courier,monospace;
    text-align: center;
    margin: 0;
    vertical-align: baseline;
    color: #222;
    cursor: pointer;
}
code {
    font-weight: bold;
    white-space: pre;
    font-family: monospace,serif;
    font-size: 1em;
}
</style>
</head>
<body onload="window.scrollBy(0, 999999);">
    <h1>Try</h1>
    <h2>Expression Calculator (0.0.1)</h2>
    <h3>PHP implementation</h3>
    <form action="try.php" method="post">
        <input type="hidden" id="action" name="action" />
        <textarea id="expression" name="expression" rows="18"><?=$expression;?></textarea>
        <input type="submit" value="Compute" onclick="document.getElementById('action').value = 0;" />
        <input type="submit" value="Generate Expression" onclick="document.getElementById('action').value = 1;" />
        <input type="button" value="Clear" onclick="document.getElementById('expression').value = '';document.getElementById('expression').focus();" />
    </form>
    <pre><?=$output;?></pre>
</body>
</html>