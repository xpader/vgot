<?php
/**
 * Created by PhpStorm.
 * User: Pader
 * Date: 2018/4/25
 * Time: 22:45
 */
use vgot\Web\Url;

?>
<form action="<?=Url::site('example/upload/upload')?>" method="post" enctype="multipart/form-data">
	<p><input type="file" name="file[]"></p>
	<p><input type="file" name="file[]"></p>
	<p><input type="submit"></p>
</form>

