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

class IndexController extends \vgot\Core\Controller
{
	
	public function index($wtf='')
	{
		$thread = new Thread();
		TransferService::hello();

		echo \vgot\app()->config->get('base_url');
		echo "Hello World\n";

		echo "\n\n";
		TransferService::test();
		echo "\n\n";
		$app = \vgot\app();
		print_r($app->config->item(''));
		echo "\n\n";
		echo $wtf;
	}

	public function caseAct($id=0)
	{
		echo 'I\'m Case Act='.$id;


	}

}