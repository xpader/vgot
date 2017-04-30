<?php

namespace vgot;

use vgot\Core\Application;
use vgot\Core\Config;
use vgot\Core\Router;
use vgot\Exceptions\HttpNotFoundException;

/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/4/23
 * Time: 01:43
 */
class Boot
{

	protected static $archPath = [];
	protected static $namespaces = [];

	/**
	 * @var Application
	 */
	protected static $application;

	/**
	 * 定义框架常规文件目录位置
	 * 
	 * @param array $path
	 */
	public static function systemConfig($path)
	{
		self::$archPath = $path;
	}

	public static function registerNamespaces($namespaces)
	{
		self::$namespaces = $namespaces;
	}

	/**
	 * Autoloader
	 *
	 * @param string $name
	 * @return bool
	 */
	public static function loadClass($name)
	{
		$arr = explode('\\', $name);
		$ns = array_shift($arr);

		//Must have a namespace
		if (count($arr) == 0) {
			return false;
		}

		if (isset(self::$namespaces[$ns])) {
			$filename = self::$namespaces[$ns].DIRECTORY_SEPARATOR.join(DIRECTORY_SEPARATOR, $arr).'.php';
			if (!is_file($filename)) {
				unset($filename);
			}
		} elseif (!empty(self::$archPath['autoload_scan_dirs'])) {
			$path = $ns.DIRECTORY_SEPARATOR.join(DIRECTORY_SEPARATOR, $arr).'.php';
			foreach (self::$archPath['autoload_scan_dirs'] as $dir) {
				if (is_file($dir.DIRECTORY_SEPARATOR.$path)) {
					$filename = $dir.DIRECTORY_SEPARATOR.$path;
					break;
				}
			}
		}

		if (isset($filename)) {
			include $filename;
			return class_exists($name, false);
		}

		return false;
	}

	/**
	 * Run Application
	 */
	public static function run()
	{
		//Register autoloads
		spl_autoload_register('\\'.self::class.'::loadClass');

		self::$application = $app = new Application();

		$app->config = new Config(self::$archPath['config_path'], self::$archPath['common_config_path']);
		$app->config->load('application');

		$app->router = new Router(self::$archPath['controller_namespace']);
		$uri = $app->router->parse();

		self::launchController($uri);
	}

	protected static function launchController($uri)
	{
		//Controller not found
		if ($uri === false) {
			throw new HttpNotFoundException();
		}

		$app = self::$application;
		$app->controller = $instance = new $uri['controller'];

		$action = !empty($uri['params'][0]) ? $uri['params'][0] : $app->config->get('default_action');

		if ($app->config->get('case_symbol')) {
			$action = $app->router->symbolConvert($action);
		}

		if (is_callable([$instance, $action])) {
			unset($uri['params'][0]);
		} elseif (is_callable([$instance, '_redirect'])) {
			$action = '_redirect';
		} else { //Action not found
			throw new HttpNotFoundException();
		}

		call_user_func_array([$instance, $action], $uri['params']);
	}

	public static function getAppInstance()
	{
		return self::$application;
	}
	
}

/**
 * 获取应用实例
 *
 * @return Application
 */
function app() {
	return Boot::getAppInstance();
}
