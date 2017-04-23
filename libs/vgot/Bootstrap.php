<?php

namespace vgot;
use vgot\Core\Application;
use vgot\Core\Config;
use vgot\Core\Router;

/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/4/23
 * Time: 01:43
 */
class Bootstrap
{

	protected static $archPath = [];
	protected static $namespaces = [];
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


	public static function run()
	{
		//Register autoloads
		spl_autoload_register('\vgot\Bootstrap::loadClass');

		$app = new Application();
		$app->config = new Config(self::$archPath['config_path'], self::$archPath['common_config_path']);
		$app->router = new Router();

		self::$application = $app;

		echo "Hello World\n";
	}

	public static function getAppInstance()
	{
		return self::$application;
	}
	
}

function app() {
	return Bootstrap::getAppInstance();
}