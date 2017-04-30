<?php

/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/4/23
 * Time: 03:21
 */
namespace app\Controllers;

class IndexController extends \vgot2\Core\Controller
{
	
	public function index()
	{
		$this->render('welcome');
	}

}