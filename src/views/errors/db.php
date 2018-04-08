<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/5/1
 * Time: 03:23
 *
 * @var $exception Exception
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<title>Database Error</title>
<style type="text/css">
body {margin:50px; background-color:#EEE; color:#505050; font-family:Arial,sans-serif;}
h1 {color:#F00;}
pre {background-color:#FFF; padding:10px; overflow:auto; font-size:14px;}
</style>
</head>
<body>
<h1>Database Error</h1>
<pre><?php throw $exception; ?></pre>
</body>
</html>