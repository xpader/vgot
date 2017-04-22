<?php

namespace vgot;

/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/4/23
 * Time: 01:43
 */
class Bootstrap
{

	protected static $archPath = [];

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
		
	}
	
	public static function run()
	{
		
	}
	
}