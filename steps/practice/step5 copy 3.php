<?php
// 第四节：生成新的语法树节点

// 在前几节中，我们学习了如何使用 php-parser 解析和修改现有的 PHP 代码的语法树节点。在本节中，我们将学习如何使用 php-parser 生成新的语法树节点，以便于创建和插入新的代码片段。

// 步骤 1: 生成新的语法树节点

// 我们将使用 php-parser 提供的节点类和方法来生成新的语法树节点。在 parser_example.php 文件中，修改代码如下：

// php
// 复制 -->


require '../../vendor/autoload.php';

<?php

$x = 5;
$y = 10;
$z = $x + $y;

echo $z;