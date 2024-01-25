<?php
require '../../vendor/autoload.php';



use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
function getAllFilePaths($directory) {
    $filePaths = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $filePaths[] = $file->getPathname();
        }
    }

    return $filePaths;
}
class PrintStatementVisitor extends NodeVisitorAbstract {
    protected $code;

    public function __construct($code) {
        $this->code = $code;
    }

    public function enterNode(Node $node) {
        if (
            0
            // $node instanceof Node\Stmt\Echo_
            // || $node instanceof Node\Expr\FuncCall && $node->name->toString() === 'print_r'
            // || $node instanceof Node\Expr\FuncCall && $node->name->toString() === 'var_dump'
            || $node instanceof Node\Expr\Exit_
            || ($node instanceof Node\Expr\FuncCall && $node->name->toString() === 'die')
        ) {
            echo "Found print or output statement:\n";
            echo "Line: " . $node->getLine() . "\n";
            echo "Comment: " . $this->getComment($node) . "\n";
            echo "Code:\n" . $this->getCodeSnippet($node) . "\n";
            echo "------------------------------------\n";
        }
    }

    protected function getComment(Node $node) {
        $startLine = $node->getStartLine();
        $comments = $node->getAttribute('comments');

        foreach ($comments as $comment) {
            if ($comment->getEndLine() === $startLine - 1) {
                return $comment->getText();
            }
        }

        return '';
    }

    protected function getCodeSnippet(Node $node) {
        $startLine = $node->getStartLine();
        $endLine = $node->getEndLine();

        $lines = explode("\n", $this->code);
        $snippet = array_slice($lines, $startLine - 1, $endLine - $startLine + 1);

        return implode("\n", $snippet);
    }
}
// 目标 PHP 代码文件路径
$targetFile = './';
if (is_dir($targetFile)){
    $files=getAllFilePaths($targetFile);
    foreach ($files as $file){
        if (is_file($file)){
            $code = file_get_contents($file);
            $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
            scanFileOutputStatement($parser, $code);
        }
    }
}else{
    $code = file_get_contents($targetFile);

    // 创建解析器
    $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
    scanFileOutputStatement($parser, $code);
}
// 读取目标 PHP 代码文件内容


/**
 * Notes
 * @author: crx
 * @time: 2024/1/25 9:48
 * @param \PhpParser\Parser $parser
 * @param $code
 * @return void
 */
function scanFileOutputStatement(\PhpParser\Parser $parser, $code): void
{
    try {
        // 解析 PHP 代码文件
        $stmts = $parser->parse($code);

        // 创建遍历器
        $traverser = new NodeTraverser();

        // 添加自定义的访问者
        $visitor = new PrintStatementVisitor($code);
        $traverser->addVisitor($visitor);

        // 遍历并应用访问者
        $stmts = $traverser->traverse($stmts);
    } catch (Error $e) {
        echo 'Parse Error: ', $e->getMessage();
    }
}

scanFileOutputStatement($parser, $code);


