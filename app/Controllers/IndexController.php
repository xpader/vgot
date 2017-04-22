<?php

/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/4/23
 * Time: 03:21
 */
namespace app\Controllers;

use app\Models\Thread;
use app\Services\TransferService;

class IndexController
{
	
	public function index()
	{
		$thread = new Thread();
		TransferService::hello();
	}

}