<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/4/23
 * Time: 01:42
 */

use vgot\Boot;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

define('BASE_PATH', realpath(__DIR__.'/..'));

ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);

require BASE_PATH.'/libs/vgot/Boot.php';
require BASE_PATH.'/vendor/autoload.php';

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

//Whoops
$run     = new Whoops\Run;
$run->pushHandler(new PrettyPageHandler);
if (Whoops\Util\Misc::isAjaxRequest()) {
	$run->pushHandler(new JsonResponseHandler);
}
$run->register();

Boot::run();
