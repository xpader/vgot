<?php
/**
 * Created by PhpStorm.
 * User: Pader
 * Date: 2018/4/25
 * Time: 22:18
 */

namespace app\Controllers\Example;

use vgot\Core\Controller;
use vgot\Web\Uploader;

class UploadController extends Controller {

	public function index()
	{
		$this->render('example/upload');
	}

	public function upload()
	{
		$uploader = new Uploader([
			'saveDir' => BASE_PATH.'/public',
			'autoName' => true,
			'overwrite' => true
		]);

		echo '-----------start----------<br>';

		if (!$uploader->validate()) {
			$errors = $uploader->getErrors();
			print_r($errors);
			echo '<br>';
		}

		echo '-------validated------<br>';

		while ($uploader->fetch() !== false) {
			$ret = $uploader->save();

			if ($ret) {
				print_r($uploader->getUploadedInfo());
			} else {
				$err = $uploader->getError();
				echo "-----error---{$err['message']}---{$err['file']['name']}---<br>";
			}

			var_dump($ret);
		}

		echo '------------end---------';
	}

	public function fetch()
	{
		$a = [1,2,3,4,5];

		echo current($a);
		next($a);

		foreach ($a as $v) {
			echo '_'.$v.'_';
		}

		reset($a);
		echo current($a);
		echo current($a);
		echo current($a);
	}

}