<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2018/4/21
 * Time: 19:43
 */

namespace app\Controllers\Test;

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
		$_SESSION['ello'] = 'test';

		$app->session->set('test', str_repeat(uniqid(), 100));
		$_SESSION['time'] = time();

		//session_destroy();

		//unset($_SESSION['test']);

		print_r($_SESSION);
	}

}