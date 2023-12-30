<?php

require '../../vendor/autoload.php';


use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

// 自定义节点访问者类
//步骤 3: 提取有用的信息
// 在 MyNodeVisitor 类的 enterNode 方法中，你可以根据节点类型提取有用的信息或执行特定的操作。例如，修改 MyNodeVisitor 类如下：
class MyNodeVisitor extends NodeVisitorAbstract
{
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Expr\FuncCall) {
            // 提取函数调用的名称
            $functionName = $node->name->toString();
            echo "Function call: $functionName\n";
        } elseif ($node instanceof Node\Stmt\Function_) {
            // 提取函数定义的名称
            $functionName = $node->name->toString();
            echo "Function definition: $functionName\n";
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

// 遍历语法树节点
$traverser->traverse($statements);