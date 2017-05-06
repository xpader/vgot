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

class MysqliDriver extends DriverInterface {

	public function connect($config)
	{
		$socket = isset($config['socket']) ? $config['socket'] : '';

		$conn = @mysqli_connect($config['host'], $config['username'], $config['password'],
			$config['database'], $config['port'], $socket);

		if (!$conn) {
			return false;
		}

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
		return ($this->conn instanceof \mysqli) && mysqli_ping($this->conn);
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
		return @mysqli_query($this->conn, $sql);
	}

	public function fetch($query, $fetchType=DB::FETCH_ASSOC)
	{
		if (!($query instanceof \mysqli_result)) {
			return false;
		}

		switch ($fetchType) {
			case DB::FETCH_ASSOC: $fetchType = MYSQLI_ASSOC; break;
			case DB::FETCH_NUM: $fetchType = MYSQLI_NUM; break;
			case DB::FETCH_BOTH: $fetchType = MYSQLI_BOTH; break;
			default: return false;
		}

		return $query->fetch_array($fetchType);
	}

	public function fetchAll($query, $fetchType=DB::FETCH_ASSOC)
	{
		if (!($query instanceof \mysqli_result)) {
			return false;
		}

		switch ($fetchType) {
			case DB::FETCH_ASSOC: $fetchType = MYSQLI_ASSOC; break;
			case DB::FETCH_NUM: $fetchType = MYSQLI_NUM; break;
			case DB::FETCH_BOTH: $fetchType = MYSQLI_BOTH; break;
			default: return false;
		}

		return $query->fetch_all($fetchType);
	}

}