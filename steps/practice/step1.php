<?php

require '../../vendor/autoload.php';

use PhpParser\ParserFactory;

// 1. 创建一个解析器实例
$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

// 2. 读取 PHP 代码文件
$code = file_get_contents('example.php');

// 3. 解析 PHP 代码
$statements = $parser->parse($code);

// 4. 打印解析得到的语句
var_dump($statements);