<?php
require_once __DIR__.'/../../vendor/autoload.php';

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

class CodeScanner
{
    public function getAllFilePaths(string $directory): array
    {
        $filePaths = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filePaths[] = $file->getPathname();
            }
        }

        return $filePaths;
    }

    public function scanCode($targetFileOrDirectory = './')
    {
        if (is_dir($targetFileOrDirectory)) {
            $files = $this->getAllFilePaths($targetFileOrDirectory);
            foreach ($files as $file) {
                $this->scanFileOutputStatement($file);
            }
        } elseif (is_file($targetFileOrDirectory) && pathinfo($targetFileOrDirectory, PATHINFO_EXTENSION) === 'php') {
            $this->scanFileOutputStatement($targetFileOrDirectory);
        } else {
            throw new \InvalidArgumentException("Invalid file or directory: $targetFileOrDirectory");
        }
    }

    private function scanFileOutputStatement(string $filePath)
    {
        $code = file_get_contents($filePath);
        $parser = $this->createParser($filePath, $code);

        try {
            // 解析 PHP 代码文件
            $stmts = $parser->parse($code);

            // 创建遍历器
            $traverser = new NodeTraverser();

            // 添加自定义的访问者
            $visitor = new PrintStatementVisitor($code, $filePath);
            $traverser->addVisitor($visitor);

            // 遍历并应用访问者
            $traverser->traverse($stmts);
        } catch (Error $e) {
            echo "Parse Error in {$filePath}: ", $e->getMessage(), PHP_EOL;
        }
    }

    private function createParser(string $filePath, string $code): \PhpParser\Parser
    {
        try {
            return (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        } catch (Error $e) {
            throw new \Exception("Failed to create parser for {$filePath}: {$e->getMessage()}");
        }
    }
}

class PrintStatementVisitor extends NodeVisitorAbstract
{
    protected $code;
    protected $filePath;

    public function __construct(string $code, string $filePath)
    {
        $this->code = $code;
        $this->filePath = $filePath;
    }

    public function enterNode(Node $node)
    {
        if (
            $node instanceof Node\Stmt\Echo_
            || $node instanceof Node\Expr\FuncCall &&
            ($node->name->toString() === 'print_r'
                || $node->name->toString() === 'var_dump')
            || $node instanceof Node\Expr\Exit_
            || ($node instanceof Node\Expr\FuncCall && $node->name->toString() === 'die')
        ) {
            echo "Found print or output statement in {$this->filePath}:{$node->getLine()}\n";
            // var_dump("$this->filePath:".$node->getLine());
            echo "Line: " . $node->getLine() . "\n";
            echo "Comment: " . $this->getComment($node) . "\n";
            echo "Code snippet:" . $this->getCodeSnippet($node) . "\n";
            echo "------------------------------------\n";
        }
    }

    protected function getComment(Node $node): string
    {
        $startLine = $node->getStartLine();
        $comments = $node->getAttribute('comments');
        if (!$comments){
            return '';
        }
        foreach ($comments as $comment) {
            if ($comment->getEndLine() === $startLine - 1) {
                return $comment->getText();
            }
        }

        return '';
    }

    protected function getCodeSnippet(Node $node): string
    {
        $startLine = $node->getStartLine();
        $endLine = $node->getEndLine();

        $lines = explode("\n", $this->code);
        $snippet = array_slice($lines, $startLine - 1, $endLine - $startLine + 1);

        return implode("\n", $snippet);
    }
}

try {
    (new CodeScanner())->scanCode(__FILE__);
} catch (\Throwable $e) {
    var_dump($e);
}
