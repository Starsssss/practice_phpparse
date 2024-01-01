<?php
require '../../vendor/autoload.php';

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;

// 创建解析器和打印器实例
$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
$printer = new PrettyPrinter\Standard;

// 要解析和修改的 PHP 代码
$code = '<?php
    $x = 1 + 2;
    var_dump($x);
    print_r("Hello, world!");
    echo $x;
    var_export($x);
';

try {
    // 解析 PHP 代码为 AST
    $stmts = $parser->parse($code);

    // 删除输出语句
    $traverser = new NodeTraverser();
    $traverser->addVisitor(new class extends PhpParser\NodeVisitorAbstract {
        public function leaveNode(Node $node) {
            var_dump('打印',$node);
            return $node;
//            if ($node instanceof Node\Stmt &&
//                ! $node instanceof Node\Stmt\Function_ &&
//                ! $node instanceof Node\Stmt\InlineHTML &&
//                ! $node instanceof Node\Stmt\Class_) {
//                return NodeTraverser::REMOVE_NODE;
//            }
//            if ($node instanceof Node\Stmt && $node instanceof Node\Expr\FuncCall) {
//                $name = $node->name;
//                var_dump($name->toString());
//                return NodeTraverser::REMOVE_NODE;
//                if ($name instanceof Node\Name && in_array(strtolower($name->toString()), ['var_dump', 'print_r', 'echo', 'var_export'], true)) {
//                    // 返回 NodeTraverser::REMOVE_NODE 标记要移除的节点
////                    return NodeTraverser::REMOVE_NODE;
//                }
//            }

            // 返回原始节点
            return $node;
        }
    });
    $nodes=$traverser->traverse($stmts);

    // 将修改后的 AST 转换为 PHP 代码
    $modifiedCode = $printer->prettyPrintFile($nodes);
//    var_dump($nodes);
//    echo "Original code:\n";
//    echo $code . PHP_EOL;

    echo "Modified code:\n";
    echo $modifiedCode . PHP_EOL;
} catch (Error $error) {
    echo "Parse error: {$error->getMessage()}\n";
}