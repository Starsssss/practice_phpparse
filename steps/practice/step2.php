<?php

require '../../vendor/autoload.php';


use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

// 自定义节点访问者类
class MyNodeVisitor extends NodeVisitorAbstract
{
    public function enterNode(Node $node)
    {
        // 打印节点类名
        echo get_class($node) . "\n";
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

// 遍历语法树节点
$traverser->traverse($statements);