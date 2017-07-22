<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/7/22
 * Time: 17:16
 */

namespace vgot\Cache;

use vgot\Exceptions\ApplicationException;

class Memcache extends Cache
{

	public $host = '127.0.0.1';
	public $port = '11211';
	public $pconnect = false;
	public $maxKeyLength = 128;

	/**
	 * @var \Memcache
	 */
	protected $memcache;

	public function __construct($config)
	{
		configClass($this, $config);
		$this->connect();
	}

	public function __destruct()
	{
		$this->close();
	}

	public function connect()
	{
		if ($this->memcache === null) {
			if (!class_exists('\Memcache')) {
				throw new ApplicationException('Server not support memcache driver.');
			}

			$obj = new \Memcache();
			$func = $this->pconnect ? 'pconnect' : 'connect';

			if (!$obj->$func($this->host, $this->port)) {
				throw new ApplicationException("Connect to memcache server failed");
			}

			$this->memcache = $obj;
		}
	}

	public function close()
	{
		if ($this->memcache) {
			$this->memcache->close();
			$this->memcache = null;
		}
	}

	public function get($key, $defaultValue=null)
	{
		$key = $this->buildKey($key);
		$value = $this->memcache->get($key);
		return $value !== false ? $value : $defaultValue;
	}

	public function set($key, $value, $duration=0)
	{
		$key = $this->buildKey($key);
		$expiredAt = $duration == 0 ? $duration : time() + $duration;
		return $this->memcache->set($key, $value, 0, $expiredAt);
	}

	public function delete($key)
	{
		$key = $this->buildKey($key);
		return $this->memcache->delete($key);
	}

	//public function __get($name)
	//{
	//	if ($name == 'memcache') {
	//		return $this->$name;
	//	}
	//
	//	throw new \ErrorException();
	//}

}