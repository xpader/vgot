<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/7/15
 * Time: 01:17
 */

namespace vgot\Cache;


abstract class Cache {

	public $keyPrefix = '';
	public $maxKeyLength = 128;
	protected $keyCompensation;

	public abstract function get($key, $defaultValue=null);

	public abstract function set($key, $data, $expire=0);

	public abstract function delete($key);

	public function buildKey($key)
	{
		if ($this->keyCompensation === null) {
			$this->keyCompensation = $this->keyPrefix ? $this->maxKeyLength - strlen($this->keyPrefix) : $this->maxKeyLength;
		}

		//Use >= not > can avoid same name as after convert
		if (strlen($key) >= $this->keyCompensation) {
			$key = substr($key, 0, $this->keyCompensation - 33).'_'.md5($key);
		}

		return $this->keyPrefix.$key;
	}

}