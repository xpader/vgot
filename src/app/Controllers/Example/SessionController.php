<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2018/4/21
 * Time: 19:43
 */

namespace app\Controllers\Example;

class SessionController extends \vgot\Core\Controller
{

	public function __init()
	{
		parent::__init();
		//getApp()->session->start();
	}

	public function index()
	{
		$app = getApp();
		$session = $app->session;

		//echo $session->get('time');
		// $_SESSION['ello'] = '来点中文怎么样';
		 //$_SESSION['test'] = str_repeat(uniqid(), 100);
		// $_SESSION['time'] = time();

		print_r($_SESSION);

		//session_destroy();

		//unset($_SESSION['test']);

		//print_r($_SESSION);
	}

}