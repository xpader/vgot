<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/4/23
 * Time: 01:42
 */

use vgot\Bootstrap;

define('BASE_PATH', __DIR__.'/..');

require BASE_PATH.'/libs/vgot/bootstrap.php';

Bootstrap::registerNamespaces([
	'app' => BASE_PATH.'/app',
]);

Bootstrap::systemConfig([
	'configPath' => BASE_PATH.'/resources/config',
	'viewPath' => BASE_PATH.'/resources/views',
	'commonConfigPath' => null,
	'commonViewsPath' => null,
	'autoloadScanDir' => [
		BASE_PATH.'/libs'
	],
	'controller_namespace' => 'app/Controllers'
]);

Bootstrap::run();
