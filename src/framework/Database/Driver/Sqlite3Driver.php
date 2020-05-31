<?php
/**
 * Created by PhpStorm.
 * User: Pader
 * Date: 2017/5/3
 * Time: 22:30
 */

namespace vgot\Database\Driver;

use SQLite3;
use vgot\Database\DB;
use vgot\Database\DriverInterface;

/**
 * Sqlite3 Database Driver
 * @package vgot\Database\Driver
 * @property SQLite3 $conn
 *
 */
class Sqlite3Driver extends DriverInterface {

	public $type = 'sqlite';

	/**
	 * @var \Exception
	 */
	protected $ex;

	public function connect($config)
	{
		$args = array($config['filename']);
		$args[] = empty($config['flags']) ? (SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE) : $config['flags'];
		$args[] = empty($config['encryption_key']) ? null : $config['encryption_key'];

		try {
			$class = new \ReflectionClass('SQLite3');
			$this->conn = $class->newInstanceArgs($args);
		} catch (\Exception $e) {
			$this->ex = $e;
			return false;
		}

		isset($config['timeout']) && $this->conn->busyTimeout($config['timeout']*1000);

		return true;
	}

	public function close()
	{
		if ($this->conn) {
			$this->conn->close();
			$this->conn = null;
		}
	}

	public function getErrorCode()
	{
		if ($this->ex) {
			return $this->ex->getCode();
		} elseif ($this->conn && $this->conn->lastErrorCode() != '00000') {
			return $this->conn->lastErrorCode();
		}

		return 0;
	}

	public function getErrorMessage()
	{
		if ($this->ex) {
			return $this->ex->getMessage();
		} elseif ($this->conn && $this->conn->lastErrorCode() != '00000') {
			return $this->conn->lastErrorMsg();
		}

		return '';
	}

	public function query($sql)
	{
		return @$this->conn->query($sql);
	}

	public function exec($sql)
	{
		return @$this->conn->exec($sql) ? $this->conn->changes() : false;
	}

	public function beginTransaction()
	{
		return $this->conn->exec('BEGIN');
	}

	public function commit()
	{
		return $this->conn->exec('COMMIT');
	}

	public function rollback()
	{
		return $this->conn->exec('ROLLBACK');
	}

	public function fetch($query, $fetchType=DB::FETCH_ASSOC)
	{
		if (!($query instanceof \SQLite3Result)) {
			return false;
		}

		$fetchType = $this->getFetchType($fetchType);
		return $query->fetchArray($fetchType) ?: null;
	}

	public function fetchAll($result, $fetchType=DB::FETCH_ASSOC)
	{
		if (!($result instanceof \SQLite3Result)) {
			return false;
		}

		$fetchType = $this->getFetchType($fetchType);
		$rows = [];
		while ($row = $result->fetchArray($fetchType)) {
			$rows[] = $row;
		}
		$result->finalize();

		return $result;
	}

	public function free($result) {
		if ($result instanceof \SQLite3Result) {
			$result->finalize();
		}
	}

	public function insertId()
	{
		return $this->conn->lastInsertRowID();
	}

	public function quote($str)
	{
		return '\''.SQLite3::escapeString($str).'\'';
	}

	protected function getFetchType($fetchType)
	{
		switch ($fetchType) {
			case DB::FETCH_ASSOC: return SQLITE3_ASSOC; break;
			case DB::FETCH_NUM: return SQLITE3_NUM; break;
			case DB::FETCH_BOTH: return SQLITE3_BOTH; break;
			default: return SQLITE3_ASSOC;
		}
	}

}