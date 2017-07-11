<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/4/23
 * Time: 01:42
 */

use vgot\Boot;

define('BASE_PATH', realpath(__DIR__.'/..'));

ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);

require BASE_PATH.'/libs/vgot/Boot.php';
require BASE_PATH.'/vendor/autoload.php';

Boot::addNamespaces([
	'app' => BASE_PATH.'/app',
]);

//Boot::addAutoloadStructs();

Boot::systemConfig([
	'controller_namespace' => '\app\Controllers',
	'config_path' => BASE_PATH.'/app/Config',
	'views_path' => BASE_PATH.'/app/Views',
	'common_config_path' => null,
	'common_views_path' => null,
]);

Boot::run();
