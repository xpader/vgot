<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/7/18
 * Time: 09:52
 */

namespace vgot\Cache;

use vgot\Database\DB;

class DbCache implements CacheInterface
{

	public $connection;
	public $table = 'cache';

	protected $db;
	protected $tableName;

	public function __construct($config=[])
	{
		configClass($this, $config);
		$this->db = DB::connection($this->connection);
		$this->tableName = $this->db->tableName($this->table);
	}

	public function get($key, $defaultValue=null)
	{
		$pk = $this->getKey($key);
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
		$pk = $this->getKey($key);
		$now = time();
		$value = serialize($value);
		$expiredAt = $duration == 0 ? $duration : $now + $duration;

		//return (bool)$this->db->exec("REPLACE INTO {$this->tableName} SET `key`=".$this->db->quote($pk).",`value`="
		//	.$this->db->quote($value).",`expired_at`=".$this->db->quote($expiredAt));

		return (bool)$this->db->insert($this->table, ['key'=>$pk, 'value'=>$value, 'expired_at'=>$expiredAt]);
	}

	public function delete($key)
	{
		$pk = $this->getKey($key);
		return (bool)$this->db->exec("DELETE FROM {$this->tableName} WHERE `key`=".$this->db->quote($pk));
	}

	public function getKey($key)
	{
		//Use >= not > can avoid same name as after convert
		if (strlen($key) >= 64) {
			$key = md5($key).'_'.substr($key, 0, 31);
		}

		return $key;
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