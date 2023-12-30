<?php
// 第四节：生成新的语法树节点

// 在前几节中，我们学习了如何使用 php-parser 解析和修改现有的 PHP 代码的语法树节点。在本节中，我们将学习如何使用 php-parser 生成新的语法树节点，以便于创建和插入新的代码片段。

// 步骤 1: 生成新的语法树节点

// 我们将使用 php-parser 提供的节点类和方法来生成新的语法树节点。在 parser_example.php 文件中，修改代码如下：

// php
// 复制 -->


require '../../vendor/autoload.php';

use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

// 创建一个解析器实例
$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

// 解析 PHP 代码文件
$code = file_get_contents('example.php');
$ast = $parser->parse($code);

// 创建代码打印器
$printer = new Standard();

// 将解析得到的语法树节点转换为 PHP 代码
$originalCode = $printer->prettyPrintFile($ast);

// 打印原始代码
echo $originalCode;