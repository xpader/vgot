<?php
/**
 * Created by PhpStorm.
 * User: Pader
 * Date: 2017/5/3
 * Time: 22:51
 */

namespace vgot\Database;


abstract class DriverInterface {

	protected $conn;

	/**
	 * Connect to database
	 *
	 * @param $config
	 * @return bool
	 */
	abstract public function connect($config);

	/**
	 * Close database connection
	 *
	 * If database is connected.
	 *
	 * @return void
	 */
	abstract public function close();

	abstract public function query($sql);

	/**
	 * Fetch one row
	 *
	 * @param mixed $query
	 * @param int $fetchType
	 * @return array|bool
	 */
	abstract public function fetch($query, $fetchType);

	abstract public function quote($string);

	abstract public function getErrorCode();

	abstract public function getErrorMessage();

	public function fetchAll($query, $fetchType)
	{
		$result = [];

		while ($row = $this->fetch($query, $fetchType)) {
			$result[] = $row;
		}

		return $result;
	}

	public function fetchColumn($query, $col, $fetchType)
	{
		$row = $this->fetch($query, $fetchType);

		if ($row) {
			return isset($row[$col]) ? $row[$col] : false;
		}

		return $row;
	}

	public function getConnection()
	{
		return $this->conn;
	}

}