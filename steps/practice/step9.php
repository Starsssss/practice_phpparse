<?php
/**
 * 改成找出所有的die，print_r
 * findInstanceOf 很好用，可以自定义查找需要的类型
 */
require '../../vendor/autoload.php';

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;

// 创建解析器实例
$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

// 要解析的 PHP 代码文件路径
$filePath = 'example.php';

try {
    // 解析 PHP 代码文件
    $code = file_get_contents($filePath);
    $stmts = $parser->parse($code);

    // 使用 NodeFinder 查找所有的 die 和 print_r 调用
    $nodeFinder = new NodeFinder();
    $calls = $nodeFinder->findInstanceOf($stmts, Node\Expr\FuncCall::class, function (Node\Expr\FuncCall $node) {
        $functionName = $node->name;

        if ($functionName instanceof Node\Name) {
            $functionName = $functionName->toString();

            return $functionName === 'die' || $functionName === 'print_r';
        }

        return false;
    });

    // 打印找到的 die 和 print_r 调用位置
    foreach ($calls as $call) {
        $functionName = $call->name;
        $startLine = $call->getStartLine();
        $endLine = $call->getEndLine();
        echo "Found $functionName call at lines $startLine-$endLine\n";
    }
} catch (Error $error) {
    echo "Parse error: {$error->getMessage()}\n";
}