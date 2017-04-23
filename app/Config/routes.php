<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 17-4-23
 * Time: 下午11:29
 */
return [
	'default_controller' => 'IndexController',
	'default_action' => 'index',
	'404_override' => false,
	'404_view' => 'errors/404',
	'case_symbol' => '-',
	'ucfirst' => true,
	'route_maps' => [
		'd/(\d+)' => 'index/case-act/$1'
	]
];