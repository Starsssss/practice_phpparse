<?php
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter;

require '../../vendor/autoload.php';

$code = '<?php
    $x = 1 + 2;
    var_dump($x);
    echo "Hello, world!";
    print_r($x);
';

// 创建解析器和打印器实例
$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
$printer = new PrettyPrinter\Standard;

// 解析 PHP 代码为 AST
$stmts = $parser->parse($code);

// 创建 NodeTraverser 实例
$traverser = new NodeTraverser();

/**
 * 判断语句是否是输出状态
 *
 * @param PhpParser\Node\Stmt\Expression| PhpParser\Node\Stmt\Echo_ $stmt 语句节点
 * @return bool 返回true表示是输出状态，否则返回false
 */
function isOutputSate(PhpParser\Node\Stmt\Expression| PhpParser\Node\Stmt\Echo_ $stmt):bool
{
    if ($stmt instanceof PhpParser\Node\Stmt\Expression){
        return isset($stmt->expr->name->parts[0])
            && in_array($stmt->expr->name->parts[0],['var_dump','echo','print_r']);
    }else if ($stmt instanceof PhpParser\Node\Stmt\Echo_){
        return isset($stmt->exprs[0]->value);
    }
    return false;
}


// 遍历节点并修改/处理节点
$modifiedStmts = $traverser->traverse($stmts);
$newArr = [];
foreach ($modifiedStmts as $modifiedStmt) {
    if (($modifiedStmt instanceof PhpParser\Node\Stmt\Expression|| $modifiedStmt instanceof  PhpParser\Node\Stmt\Echo_) &&  isOutputSate($modifiedStmt)){
        var_dump(111111);
    }else{
        $newArr[]=$modifiedStmt;
    }
}
// 将修改后的 AST 转换为 PHP 代码
$modifiedCode = $printer->prettyPrintFile($newArr);

echo "Original code:\n";
echo $code . PHP_EOL;

echo "Modified code:\n";
echo $modifiedCode . PHP_EOL;