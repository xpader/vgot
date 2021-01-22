<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/7/22
 * Time: 18:39
 */

namespace vgot\Cache;


use vgot\Exceptions\ApplicationException;

class Redis extends Cache {

	public $host;
	public $port;
	public $password;
	public $pconnect = false;
	public $database;

	/**
	 * How to serialize data for storage in redis
	 *
	 * Can set: php, igbinary or none.
	 * igbinary need to install igbinary extension, and redis extension must complie with configure --enable-redis-igbinary.
	 *
	 * @var string
	 */
	public $serialize = 'php';

	protected $serializeFunc;
	protected $unserializeFunc;

	/**
	 * @var \Redis
	 */
	public $redis;

	public function __construct($host, $port=6379, $password=null, $pconnect=false, $database=null)
	{
		$this->host = $host;
		$this->port = $port;
		$this->password = $password;
		$this->pconnect = $pconnect;
		$this->database = $database;
		$this->connect();
	}

	public function __destruct()
	{
		$this->close();
	}

	public function connect()
	{
		if ($this->redis === null) {
			if (!class_exists('\Redis')) {
				throw new ApplicationException('Server not support redis, please install <a href="http://pecl.php.net/package/redis" target="_blank">redis extension</a>.');
			}

			$obj = new \Redis();
			$func = $this->pconnect ? 'pconnect' : 'connect';

			if (!@$obj->$func($this->host, $this->port)) {
				throw new ApplicationException("Connect to redis server failed!");
			}

			if ($this->password && !$obj->auth($this->password)) {
				throw new ApplicationException("Authenticate the redis connection failed!");
			}

			if ($this->database !== null) {
				$obj->select($this->database);
			}

			//Option serialize
			switch (strtolower($this->serialize)) {
				case 'php':
					$obj->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
					break;
				//case 'json':
				//	$this->serializeFunc = function($val) { return json_encode($val, JSON_UNESCAPED_UNICODE); };
				//	$this->unserializeFunc = function($val) { return json_decode($val, true); };
				//	break;
				case 'igbinary':
					if (defined('\Redis::SERIALIZER_IGBINARY')) {
						//configure with --enable-redis-igbinary
						$obj->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_IGBINARY);
					//} elseif (extension_loaded('igbinary')) {
					//	$this->serializeFunc = 'igbinary_serialize';
					//	$this->unserializeFunc = 'igbinary_unserialize';
					} else {
						throw new ApplicationException('Redis not support igbinary serialize now, redis extension must configure with --enable-redis-igbinary.');
					}
					break;
				//case 'msgpack':
				//	if (extension_loaded('msgpack')) {
				//		$this->serializeFunc = 'msgpack_pack';
				//		$this->unserializeFunc = 'msgpack_unpack';
				//	} else {
				//		throw new ApplicationException('Redis msgpack serialize need to load <a href="http://pecl.php.net/package/msgpack" target="_blank">msgpack extension</a>.');
				//	}
				//	break;
				case 'none': break;
				default:
					throw new ApplicationException("Unsupported serialize type '{$this->serialize}' for redis!");
			}

			$this->redis = $obj;
		}
	}

	public function close()
	{
		if ($this->redis) {
			$this->redis->close();
			$this->redis = null;
		}
	}

	public function get($key, $defaultValue=null)
	{
		$key = $this->buildKey($key);
		$value = $this->redis->get($key);
		return $value !== false ?
			($this->unserializeFunc ? call_user_func($this->unserializeFunc, $value) : $value)
			: $defaultValue;
	}

	public function set($key, $value, $duration=0)
	{
		$key = $this->buildKey($key);

		$this->serializeFunc && $value = call_user_func($this->serializeFunc, $value);

		if ($duration == 0) {
			return $this->redis->set($key, $value);
		} else {
			return $this->redis->setex($key, $duration, $value);
		}
	}

	public function delete($key)
	{
		$key = $this->buildKey($key);
		return $this->redis->del($key);
	}

}