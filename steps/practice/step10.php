<?php
/**
 * @link https://ucw.moe/archives/php-parser-ast.html
 *  操作 AST
 * PHP-Parser 提供了 NodeVisitor 和 NodeFinder 两种遍历节点的接口，后者是前者的一种简洁用法，本质上它们都需要使用 NodeTraverser。这里我们以适用范围更广的 NodeVisitor 举例。
 *
 * AST 就像是一把精巧的手术刀，许多大规模改动源代码的问题都可以通过它简便地解决。操作 AST 时就像是在对源代码动手术，以节点为单位批量解决许多看似难以解决的问题。
 *
 * 例如，我们需要把代码中所有的十六进制数字换成十进制书写，便于维护，在源代码很大时这个工作将会很困难，但利用 PHP-Parser 即可轻松处理。我们用 NodeVisitor 处理一下：
 */
require '../../vendor/autoload.php';

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\NodeDumper;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

// 创建解析器实例
$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

// 要解析的 PHP 代码文件路径
$filePath = 'example.php';


    // 解析 PHP 代码文件
    $code = file_get_contents($filePath);

    $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
    try {
        $ast = $parser->parse($code);
    } catch (Error $error) {
        echo "Parse error: {$error->getMessage()}\n";
        return;
    }

    $dumper = new NodeDumper;
    echo $dumper->dump($ast) . "\n";
$traverser = new NodeTraverser();
    $traverser->addVisitor(new class extends NodeVisitorAbstract {
        public function leaveNode(Node $node) {
            if ($node instanceof LNumber) { // 十六进制数字修复为十进制
                return new LNumber(888866666);
//                return new LNumber($node->value,array('kind'=>LNumber::KIND_DEC));
            }
        }
    });
    $ast = $traverser->traverse($ast);
// 创建代码打印器
$printer = new Standard();
// 将修改后的语法树节点转换为 PHP 代码
$modifiedCode = $printer->prettyPrintFile($ast);

// 打印修改后的代码
echo $modifiedCode;