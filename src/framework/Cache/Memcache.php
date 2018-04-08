<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/7/22
 * Time: 17:16
 */

namespace vgot\Cache;

use vgot\Exceptions\ApplicationException;

/**
 * Memcache for Cache
 *
 * @package vgot\Cache
 * @method add($key, $value, $duration=0):bool Add an item if key not exists.
 * @method replace($key, $value, $duration=0):bool Replace an item if key exists.
 * @method decrement($key, $value=1):bool
 * @method increment($key, $value=1):bool
 */
class Memcache extends Cache {

	public $host;
	public $port = 11211;
	public $pconnect = false;
	public $flag = 0;

	/**
	 * @var \Memcache
	 */
	public $memcache;

	public function __construct($config)
	{
		configClass($this, $config);
		$this->connect();
	}

	public function __destruct()
	{
		$this->close();
	}

	//public function __call($name, $args) {
	//	$call = [$this->memcache, $name];
	//
	//	if (!is_callable($call)) {
	//		throw new \ErrorException("Call to undefined method: ".__CLASS__."::$$name()");
	//		//trigger_error("Call to undefined method: ".__CLASS__."::$$name()", E_USER_ERROR);
	//	}
	//
	//	switch ($name) {
	//		case 'add':
	//		case 'replace':
	//			$args[2] = $this->flag; //flag
	//			$args[3] = empty($args[3]) ? 0 : time() + $args[3]; //duration
	//		case 'decrement':
	//		case 'increment':
	//			$args[0] = $this->buildKey($args[0]); //key
	//			break;
	//	}
	//
	//	return call_user_func_array($call, $args);
	//}

	public function connect()
	{
		if ($this->memcache === null) {
			if (!class_exists('\Memcache')) {
				throw new ApplicationException('Server not support memcache, please install <a href="http://pecl.php.net/package/memcache" target="_blank">memcache extension</a>.');
			}

			$obj = new \Memcache();
			$func = $this->pconnect ? 'pconnect' : 'connect';

			if (!@$obj->$func($this->host, $this->port)) {
				throw new ApplicationException("Connect to memcache server failed! Check the network, firewall or server status.");
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
		$value = $this->memcache->get($key, $this->flag);
		return $value !== false ? $value : $defaultValue;
	}

	public function set($key, $value, $duration=0)
	{
		$key = $this->buildKey($key);
		$expiredAt = $duration == 0 ? $duration : time() + $duration;
		return $this->memcache->set($key, $value, $this->flag, $expiredAt);
	}

	public function delete($key)
	{
		$key = $this->buildKey($key);
		return $this->memcache->delete($key);
	}

}