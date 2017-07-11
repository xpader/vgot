<?php

namespace vgot;

use vgot\Core\Application;

/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/4/23
 * Time: 01:43
 */
class Boot
{

	protected static $archPath = [];
	protected static $namespaces = [
		__NAMESPACE__ => __DIR__
	];
	protected static $autoloadStructs = [];

	/**
	 * 定义框架常规文件目录位置
	 * 
	 * @param array $path
	 */
	public static function systemConfig($path)
	{
		self::$archPath = $path;
	}

	public static function addNamespaces($namespaces)
	{
		self::$namespaces += $namespaces;
	}

	public static function addAutoloadStructs($structs)
	{
		if (is_array($structs)) {
			self::$autoloadStructs = array_merge(self::$autoloadStructs, $structs);
		} else {
			self::$autoloadStructs[] = $structs;
		}
	}

	/**
	 * Autoloader
	 *
	 * @param string $name
	 */
	public static function loadClass($name)
	{
		$arr = explode('\\', $name);
		$ns = array_shift($arr);

		//Must have a namespace
		if (count($arr) == 0) {
			return;
		}

		if (isset(self::$namespaces[$ns])) {
			$filename = self::$namespaces[$ns].DIRECTORY_SEPARATOR.join(DIRECTORY_SEPARATOR, $arr).'.php';
			if (!is_file($filename)) {
				unset($filename);
			}
		} elseif (self::$autoloadStructs) {
			$path = $ns.DIRECTORY_SEPARATOR.join(DIRECTORY_SEPARATOR, $arr).'.php';
			foreach (self::$autoloadStructs as $dir) {
				if (is_file($dir.DIRECTORY_SEPARATOR.$path)) {
					$filename = $dir.DIRECTORY_SEPARATOR.$path;
					break;
				}
			}
		}

		if (isset($filename)) {
			include $filename;
		}
	}

	/**
	 * Run Application
	 */
	public static function run()
	{
		//Register autoloads
		spl_autoload_register(self::class.'::loadClass');

		$app = new Application(self::$archPath);

		//Call custom error handler
		$setErrorHandler = $app->config->get('set_error_handler');
		if (is_callable($setErrorHandler)) {
			$setErrorHandler();
		} else {
			//set_error_handler('\vgot\Core\ErrorHandler::errorHandler');
			set_exception_handler('\vgot\Core\ErrorHandler::exceptionHandler');
			//register_shutdown_function('\vgot\Core\ErrorHandler::shutdownHandler');
		}

		//Startup application
		$app->execute();
	}

}

/**
 * 获取应用实例
 *
 * @return Application
 */
function app() {
	return Application::getInstance();
}

function helper($helper) {
}
