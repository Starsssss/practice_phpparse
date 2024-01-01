<?php
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter;

require '../../vendor/autoload.php';

$code = '<?php
    $x = 1 + 2;
    $y = $x * 3;
    echo $y;
';

// 创建解析器和打印器实例
$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
$printer = new PrettyPrinter\Standard;

// 解析 PHP 代码为 AST
$stmts = $parser->parse($code);

class MyNodeVisitor extends NodeVisitorAbstract
{
    public function leaveNode($node)
    {
        // 删除所有的变量声明语句
        if ($node instanceof PhpParser\Node\Stmt\Expression && $node->expr instanceof PhpParser\Node\Expr\Assign
            && $node->expr->var instanceof PhpParser\Node\Expr\Variable) {
            return NodeTraverser::REMOVE_NODE;
        }

        // 返回原始节点
        return $node;
    }
}

// 创建 NodeTraverser 实例
$traverser = new NodeTraverser();

// 添加自定义的访问器到遍历器中
$traverser->addVisitor(new MyNodeVisitor());

// 遍历节点并修改/处理节点
$modifiedStmts = $traverser->traverse($stmts);

// 将修改后的 AST 转换为 PHP 代码
$modifiedCode = $printer->prettyPrintFile($modifiedStmts);

echo "Original code:\n";
echo $code . PHP_EOL;

echo "Modified code:\n";
echo $modifiedCode . PHP_EOL;