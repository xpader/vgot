<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/4/23
 * Time: 01:42
 */

use vgot\Boot;

define('BASE_PATH', realpath(__DIR__.'/..')); //constant only for app

ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);

require BASE_PATH.'/framework/Boot.php';
require BASE_PATH.'/vendor/autoload.php';

Boot::addNamespaces([
	'app' => BASE_PATH.'/app',
]);

//Boot::addAutoloadStructs([BASE_PATH, BASE_PATH.'/src']);

Boot::systemConfig([
	'controller_namespace' => '\app\Controllers',
	'config_path' => BASE_PATH.'/config',
	'views_path' => BASE_PATH.'/views',
	'common_config_path' => null,
	'common_views_path' => null,
]);

Boot::run();
