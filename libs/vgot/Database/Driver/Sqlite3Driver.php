<?php
/**
 * Created by PhpStorm.
 * User: Pader
 * Date: 2017/5/3
 * Time: 22:30
 */

namespace vgot\Database\Driver;

use SQLite3;
use vgot\Database\DriverInterface;

/**
 * Sqlite3 Database Driver
 * @package vgot\Database\Driver
 * @property SQLite3 $conn
 *
 */
class Sqlite3Driver extends DriverInterface {

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
		} elseif ($this->conn && $this->conn->errorCode() != '00000') {
			return $this->conn->errorCode();
		}

		return 0;
	}

	public function getErrorMessage()
	{
		if ($this->ex) {
			return $this->ex->getMessage();
		} elseif ($this->conn && $this->conn->errorCode() != '00000') {
			$info = $this->conn->errorInfo();
			return $info[2];
		}

		return '';
	}

}