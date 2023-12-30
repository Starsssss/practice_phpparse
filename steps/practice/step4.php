<?php

require '../../vendor/autoload.php';

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
/**
 * 第三节：修改语法树节点

在前两节中，我们学习了如何使用 php-parser 解析 PHP 代码并遍历语法树节点。在本节中，我们将学习如何修改语法树节点，并将修改后的语法树重新转换回 PHP 代码。
 */
// 自定义节点访问者类
class MyNodeVisitor extends NodeVisitorAbstract
{
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Expr\FuncCall) {
            // 修改函数调用的名称为大写
            $node->name = new Node\Name('NEW_FUNCTION');
        }
    }
}

// 1. 创建一个解析器实例
$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

// 2. 读取 PHP 代码文件
$code = file_get_contents('example.php');

// 3. 解析 PHP 代码
$statements = $parser->parse($code);

// 创建节点遍历器
$traverser = new NodeTraverser();

// 注册自定义节点访问者
$traverser->addVisitor(new MyNodeVisitor());

// 修改语法树节点
$modifiedStatements = $traverser->traverse($statements);

// 创建代码打印器
$printer = new Standard();

// 将修改后的语法树转换回 PHP 代码
$modifiedCode = $printer->prettyPrintFile($modifiedStatements);

// 打印修改后的代码
echo $modifiedCode;