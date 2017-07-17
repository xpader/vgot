<?php

namespace vgot\Cache\Driver;

use vgot\Cache\DriverInterface;

/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/7/15
 * Time: 01:18
 */
class FileDriver implements DriverInterface {

	public $basePath;
	public $dirLevel = 0;

	public function __construct($config)
	{

	}

	public function get($key, $defaultValue=null)
	{
		$file = $this->getFilename($key);

		if (is_file($file)) {

		} else {
			return $defaultValue;
		}
	}

	public function set($key, $data, $expire=0)
	{}

	public function delete($key)
	{

	}


	protected function getFilename($key)
	{
		$hash = md5($key);
		$path = '';

		if ($this->dirLevel > 0) {
			$seg = 2 * $this->dirLevel;
			$prefix = str_split(substr($hash, 0, $seg), 2);
			$path = join(DIRECTORY_SEPARATOR, $prefix).DIRECTORY_SEPARATOR.substr($hash, $seg);
		}

		$key = str_replace(['#', '^', ':', ' '], '_', $key);
		$path .= '_'.$key;

		if (strlen($path) > 60) {
			$path = substr($path, 0, 60);
		}

		return $path.'.php';
	}

}