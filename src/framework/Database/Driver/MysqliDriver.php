<?php
/**
 * Created by PhpStorm.
 * User: Pader
 * Date: 2017/5/3
 * Time: 22:29
 */

namespace vgot\Database\Driver;

use vgot\Database\DB;
use vgot\Database\DriverInterface;

/**
 * Class MysqliDriver
 * @property \mysqli $conn
 */
class MysqliDriver extends DriverInterface {

	public $type = 'mysql';

	public function connect($config)
	{
		$socket = isset($config['socket']) ? $config['socket'] : '';

		$conn = @mysqli_connect($config['host'], $config['username'], $config['password'],
			$config['database'], $config['port'], $socket);

		if (!$conn) {
			return false;
		}

		isset($config['timeout']) && mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, $config['timeout']);

		$this->conn = $conn;
		return true;
	}

	public function close()
	{
		if ($this->conn) {
			@mysqli_close($this->conn);
			$this->conn = null;
		}
	}

	public function ping()
	{
		return ($this->conn instanceof \mysqli) && $this->conn->ping();
	}

	public function getErrorCode()
	{
		return $this->conn ? mysqli_errno($this->conn) : mysqli_connect_errno();
	}

	public function getErrorMessage()
	{
		return $this->conn ? mysqli_error($this->conn) : mysqli_connect_error();
	}

	public function query($sql)
	{
		return $this->conn->query($sql);
	}

	public function exec($sql)
	{
		return $this->conn->real_query($sql) ? $this->conn->affected_rows : false;
	}

	public function beginTransaction()
	{
		return PHP_VERSION_ID >= 50500 ? $this->conn->begin_transaction() : $this->conn->real_query('BEGIN');
	}

	public function commit()
	{
		return $this->conn->commit();
	}

	public function rollback()
	{
		return $this->conn->rollback();
	}

	public function fetch($query, $fetchType=DB::FETCH_ASSOC)
	{
		if (!($query instanceof \mysqli_result)) {
			return false;
		}

		$fetchType = $this->getFetchType($fetchType);
		return $query->fetch_array($fetchType);
	}

	public function fetchAll($query, $fetchType=DB::FETCH_ASSOC)
	{
		if (!($query instanceof \mysqli_result)) {
			return false;
		}

		$fetchType = $this->getFetchType($fetchType);
		return $query->fetch_all($fetchType);
	}

	public function insertId()
	{
		return $this->conn->insert_id;
	}

	public function quote($str)
	{
		return '\''.$this->conn->real_escape_string($str).'\'';
	}

	protected function getFetchType($fetchType)
	{
		switch ($fetchType) {
			case DB::FETCH_ASSOC: return MYSQLI_ASSOC; break;
			case DB::FETCH_NUM: return MYSQLI_NUM; break;
			case DB::FETCH_BOTH: return MYSQLI_BOTH; break;
			default: return MYSQLI_ASSOC;
		}
	}

}