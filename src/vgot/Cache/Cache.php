<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/7/10
 * Time: 15:40
 */

namespace vgot\Cache;

use vgot\Exceptions\ApplicationException;


class Cache
{

	/**
	 * @var DriverInterface
	 */
	protected $di;

	public function __construct($driver='file', $config=[])
	{
		$driverClass = 'vgot\Cache\Driver\\'.ucfirst($driver).'Driver';

		if (!class_exists($driverClass)) {
			throw new ApplicationException("Unsupport cache driver '{$driver}'.");
		}

		$this->di = new $driverClass($config);
	}

	public function get($key, $defaultValue=null)
	{
		return $this->di->get($key, $defaultValue);
	}

	public function set($key, $value, $duration=0)
	{
		return $this->di->set($key, $value, $duration);
	}

	public function delete($key)
	{
		return $this->di->delete($key);
	}

	public function getDriver()
	{
		return $this->di;
	}

}