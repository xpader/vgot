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
<title><?=htmlspecialchars($exception->getMessage())?></title>
<style type="text/css">
body {margin:50px; background-color:#EEE; color:#505050; font-family:Arial,sans-serif;}
h1 {color:#F00;}
pre {background-color:#FFF; padding:10px; overflow:auto; font-size:14px;}
</style>
</head>
<body>
<h1>500 Internal Server Error</h1>
<pre><h2><?=$exception->getMessage()?></h2>
at: <?=$exception->getFile()?> on line <?=$exception->getLine()?></pre>
<div>
    <table>
        <thead>
        <tr>
            <th></th>
            <th>File</th>
            <th>Line</th>
            <th>Caller</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($exception->getTrace() as $i => $row) { ?>
            <tr>
                <td>#<?=$i?></td>
                <?php if (isset($row['file'])) { ?>
                    <td><?=$row['file']?></td>
                    <td><?=$row['line']?></td>
                <?php } else { ?>
                    <td>[internal function]</td>
                    <td>-</td>
                <?php } ?>
                <td><?php if (isset($row['class'])) { echo $row['class'].$row['type']; } ?><?=$row['function']?>()</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>