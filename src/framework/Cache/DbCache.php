<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/7/18
 * Time: 09:52
 */

namespace vgot\Cache;

use vgot\Database\DB;

class DbCache extends Cache {

	public $connection;
	public $table = 'cache';
	public $gcProbability = 10; //0.001%

	protected $db;
	protected $tableName;

	public function __construct($config=[])
	{
		configClass($this, $config);
		$this->db = DB::connection($this->connection);
		$this->tableName = $this->db->tableName($this->table);
		$this->maxKeyLength = 64;
		$this->keyPrefix = null;
	}

	public function get($key, $defaultValue=null)
	{
		$pk = $this->buildKey($key);
		$data = $this->db->query("SELECT `value`,`expired_at` FROM {$this->tableName} WHERE `key`="
			.$this->db->quote($pk))->fetch();

		if ($data) {
			$now = time();
			if ($data['expired_at'] == 0 || $data['expired_at'] > $now) {
				return unserialize($data['value']);
			}
			$this->delete($key);
		}

		return $defaultValue;
	}

	public function set($key, $value, $duration=0)
	{
		$this->gc();

		$pk = $this->buildKey($key);
		$now = time();
		$value = serialize($value);
		$expiredAt = $duration == 0 ? $duration : $now + $duration;

		return (bool)$this->db->exec("REPLACE INTO {$this->tableName} SET `key`=".$this->db->quote($pk).",`value`="
			.$this->db->quote($value).",`expired_at`=".$this->db->quote($expiredAt));
	}

	public function delete($key)
	{
		$pk = $this->buildKey($key);
		return (bool)$this->db->exec("DELETE FROM {$this->tableName} WHERE `key`=".$this->db->quote($pk));
	}

	/**
	 * Garbage Collection
	 * Remove expired cache data.
	 *
	 * @param bool $force
	 */
	public function gc($force=false)
	{
		if ($force || mt_rand(0, 1000000) < $this->gcProbability) {
			$expire = time();
			$this->db->exec("DELETE FROM {$this->tableName} WHERE `expired_at` BETWEEN 1 AND $expire");
		}
	}

	public function createTable()
	{
		$this->db->query('CREATE TABLE '.$this->tableName. '('
			.'`key` CHAR(64) NOT NULL PRIMARY KEY,'
			.'`value` MEDIUMTEXT NOT NULL,'
			.'`expired_at` INT(10) UNSIGNED NOT NULL'
			.') ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT=\'Cache\'');
	}

}