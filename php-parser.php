
<?php
require 'vendor/autoload.php';
use PhpParser\Builder\Method;
use PhpParser\Builder\Use_;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
// use Exception;
class Parse{
/** 略 */
    public function handle()
    {
        // 解析需测试文件
        $namespaces = $this->parseFile(("./a.php"));
        array_walk($namespaces, function ($namespace) {
            [$namespace, $classes] = $namespace;

            // 解析文件中的类
            foreach ($classes as $class) {
                $className = $class->name->name;
                $testClassName = "{$className}Test";
                $testFilePath = ("tests/Unit/{$testClassName}.php");

                $classMethodNames = array_filter(array_map(function ($bodyPart) {
                    if ($bodyPart instanceof ClassMethod && $bodyPart->isPublic() && $bodyPart->isStatic()) {
                        $methodName = $bodyPart->name->name;
                        return 'test' . strtoupper($methodName[0]) . substr($methodName, 1);
                    }
                    return null;
                }, $class->stmts));

                if (file_exists($testFilePath)) {
                    // 读取已添加的测试
                    [$testNamespace, $testClassMethodNames, $testClass] = $this->getExistsTest($testFilePath);
                    $todoMethodNames = array_diff($classMethodNames, $testClassMethodNames);
                } else {
                    // 创建空的测试类
                    $testNamespace = $this->prepareTestFile('Test\Unit');
                    $todoMethodNames = $classMethodNames;
                    $testClass = new Class_($testClassName);
                    $testClass->extends = new Node\Name('TestCase');
                }

                $testNamespace->stmts = array_filter($testNamespace->stmts, function ($stmt) {
                    return !($stmt instanceof Class_);
                });

                $testClass->stmts = array_merge($testClass->stmts, array_map(function ($methodName) {
                    $method = new Method($methodName);
                    $method->makePublic();

                    return $method->getNode();
                }, $todoMethodNames));

                $testNamespace->stmts[] = $testClass;

                $prettyPrinter = new PrettyPrinter\Standard;
                echo $prettyPrinter->prettyPrintFile([$testNamespace]);
            }
        });
    }

    /**
     * 解析已有的测试
     *
     * @param $testFilePath
     * @return array
     * @throws Exception
     */
    protected function getExistsTest($testFilePath)
    {
        $testClasses = $this->parseFile($testFilePath);
        if (count($testClasses) !== 1) {
            throw new Exception('测试文件需有且仅有一个PHP片段');
        }
        [$testNamespace, $testClasses] = $testClasses[0];
        if (count($testClasses) !== 1) {
            throw new Exception('测试文件需有且仅有一个类');
        }
        $testClass = $testClasses[0];

        $testClassMethodNames = array_filter(array_map(function ($bodyPart) {
            if ($bodyPart instanceof ClassMethod && $bodyPart->isPublic()) {
                return $bodyPart->name->name;
            }
            return null;
        }, $testClass->stmts));

        return [$testNamespace, $testClassMethodNames, $testClass];
    }

    protected function prepareTestFile($namespace)
    {
        $namespace = new \PhpParser\Builder\Namespace_($namespace);
        $namespace->addStmt(new Use_('Tests\TestCase', Node\Stmt\Use_::TYPE_NORMAL));

        return $namespace->getNode();
    }

    protected function parseFile($path)
    {
        $code = file_get_contents($path);
        var_dump($code);
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($code);
        if (!count($ast)) {
            throw new Exception('未发现php代码');
        }
        $namespaces = $this->parsePHPSegments($ast);

        return $namespaces;
    }

    protected function parsePHPSegments($segments)
    {
        $segments = array_filter($segments, function ($segment) {
            return $segment instanceof Namespace_;
        });

        $segments = array_map(function (Namespace_ $segment) {
            return [$segment, $this->parseNamespace($segment)];
        }, $segments);

        return $segments;
    }

    protected function parseNamespace(Namespace_ $namespace)
    {
        $classes = array_values(array_filter($namespace->stmts, function ($class) {
            return $class instanceof Class_;
        }));

        return $classes;
    }
}

(new Parse())->handle();