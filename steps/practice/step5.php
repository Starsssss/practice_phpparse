<?php
// 第四节：生成新的语法树节点

// 在前几节中，我们学习了如何使用 php-parser 解析和修改现有的 PHP 代码的语法树节点。在本节中，我们将学习如何使用 php-parser 生成新的语法树节点，以便于创建和插入新的代码片段。

// 步骤 1: 生成新的语法树节点

// 我们将使用 php-parser 提供的节点类和方法来生成新的语法树节点。在 parser_example.php 文件中，修改代码如下：

// php
// 复制 -->


require '../../vendor/autoload.php';


use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Scalar\String_;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

// 创建一个解析器实例
$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

// 创建代码打印器
$printer = new Standard();

// 创建新的字符串节点
$newString = new String_('Hello, World!');

// 创建新的 echo 语句节点
$newEchoStatement = new Echo_([$newString]);

// 将新的语句节点转换为 PHP 代码
$newCode = $printer->prettyPrint([$newEchoStatement]);

// 打印新的代码
echo $newCode;

// 步骤 2: 运行代码

// 保存并运行修改后的 parser_example.php 文件，你将看到生成的新的 PHP 代码被打印出来。

// 复制
// php parser_example.php