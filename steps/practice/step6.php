<?php
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
// echo $originalCode;


// 查找变量赋值节点并修改其初始值
/**
 * 在这段代码中，我们遍历语法树节点并找到与 $x 变量相关的赋值节点。一旦找到匹配的节点，我们将其初始值修改为 10。然后，我们使用代码打印器将修改后的语法树节点转换为 PHP 代码。
 */
foreach ($ast as $node) {
    if ($node instanceof \PhpParser\Node\Stmt\Expression
        && $node->expr instanceof \PhpParser\Node\Expr\Assign
        && $node->expr->var instanceof \PhpParser\Node\Expr\Variable
        && $node->expr->var->name === 'x'
    ) {
        $node->expr->expr = new \PhpParser\Node\Scalar\LNumber(10);
    }
}

// 将修改后的语法树节点转换为 PHP 代码
$modifiedCode = $printer->prettyPrintFile($ast);

// 打印修改后的代码
echo $modifiedCode;

// 将修改后的代码保存到文件
// file_put_contents('modified_example1.php', $modifiedCode);
// echo "Modified code saved to modified_example.php";
// 在当前环境中执行修改后的 PHP 代码
$res=eval($modifiedCode);
var_dump($res);