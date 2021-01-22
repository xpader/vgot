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

	/**
	 * @var string
	 */
	public $connection;

	/**
	 * @var string
	 */
	public $table;

	/**
	 * @var int
	 */
	public $maxKeyLength = 64;

	/**
	 * @var int
	 */
	public $gcProbability = 10; //0.001%

	protected $db;
	protected $tableName;

	/**
	 * DbCache constructor.
	 * @param string $table
	 * @param string $connection
	 * @param int $gcProbability Garbage collect probability, 10 mean 0.001%
	 * @throws \vgot\Exceptions\DatabaseException
	 */
	public function __construct($table='cache', $connection=null, $gcProbability=10)
	{
		$this->table = $table;
		$this->connection = $connection;
		$this->gcProbability = $gcProbability;

		//To use DbCache, the db connection must use query_builder.
		$this->db = DB::connection($this->connection, true);
		$this->tableName = $this->db->tableName($this->table);
		$this->maxKeyLength = 64;
		$this->keyPrefix = null;
	}

	public function get($key, $defaultValue=null)
	{
		$pk = $this->buildKey($key);
		$data = $this->db->select('value,expired_at')->from($this->tableName)->where(['key'=>$pk])->fetch();

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

		return (bool)$this->db->insert($this->tableName, ['key'=>$pk, 'value'=>$value, 'expired_at'=>$expiredAt], true);
	}

	public function delete($key)
	{
		$pk = $this->buildKey($key);
		return (bool)$this->db->where(['key'=>$pk])->delete($this->tableName);
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
			$this->db->where(['expired_at between'=>[1, $expire]])->delete($this->tableName);
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