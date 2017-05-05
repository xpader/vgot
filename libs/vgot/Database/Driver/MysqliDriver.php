<?php
/**
 * Created by PhpStorm.
 * User: Pader
 * Date: 2017/5/3
 * Time: 22:29
 */

namespace vgot\Database\Driver;

use vgot\Database\DriverInterface;
use vgot\Exceptions\DatabaseException;

class MysqliDriver extends DriverInterface {

	protected $conn;

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
		return ($this->conn instanceof mysqli) && mysqli_ping($this->conn);
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

}