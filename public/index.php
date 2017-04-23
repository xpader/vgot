<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/4/23
 * Time: 01:42
 */

use vgot\Bootstrap;

define('BASE_PATH', realpath(__DIR__.'/..'));

require BASE_PATH.'/libs/vgot/Bootstrap.php';

Bootstrap::registerNamespaces([
	'app' => BASE_PATH.'/app',
]);

Bootstrap::systemConfig([
	'controller_namespace' => '\app\Controllers',
	'config_path' => BASE_PATH.'/app/Config',
	'view_path' => BASE_PATH.'/app/Views',
	'autoload_scan_dirs' => [
		BASE_PATH.'/libs'
	],
	'common_config_path' => null,
	'common_ciews_path' => null,
]);

Bootstrap::run();
