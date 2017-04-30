<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/4/23
 * Time: 01:42
 */

use vgot\Boot;

define('BASE_PATH', realpath(__DIR__.'/..'));

require BASE_PATH.'/libs/vgot/Boot.php';

Boot::registerNamespaces([
	'app' => BASE_PATH.'/app',
]);

Boot::systemConfig([
	'controller_namespace' => '\app\Controllers',
	'config_path' => BASE_PATH.'/app/Config',
	'views_path' => BASE_PATH.'/app/Views',
	'autoload_scan_dirs' => [
		BASE_PATH.'/libs'
	],
	'common_config_path' => null,
	'common_views_path' => null,
]);

Boot::run();
