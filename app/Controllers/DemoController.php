<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 17-4-24
 * Time: 上午1:43
 */
namespace app\Controllers;

use vgot\Database\DB;

class DemoController extends \vgot\Core\Controller
{

	public function index()
	{
		echo 'WhatsApp';
	}

	public function dev()
	{
		echo 'This is dev';
	}

	public function gzip()
	{
		echo '<div>'.str_repeat('Hello World ', 500).'</div>';
		echo '<div>'.str_repeat('Hello World ', 500).'</div>';
	}

	public function view()
	{
		$this->render('index/index');
	}

	public function db()
	{
		$db = DB::connection();

		var_dump($db);

		$app = \vgot\app();
		$app->register('db', $db);

		echo 'Hello World';


		$app->register('db', $db);
	}

}