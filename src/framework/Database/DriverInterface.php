<?php
/**
 * Created by PhpStorm.
 * User: Pader
 * Date: 2017/5/3
 * Time: 22:51
 */

namespace vgot\Database;


abstract class DriverInterface {

	public $type;
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

	/**
	 * Do a query and return query result
	 *
	 * @param string $sql
	 * @return mixed
	 */
	abstract public function query($sql);

	/**
	 * Do a query and return affected rows number
	 *
	 * @param string$sql
	 * @return int|false
	 */
	abstract public function exec($sql);

	/**
	 * Initiates a transaction
	 *
	 * @return bool
	 */
	abstract public function beginTransaction();

	/**
	 * Commit a transaction
	 *
	 * @return bool
	 */
	abstract public function commit();

	/**
	 * Rollback a transaction
	 *
	 * @return bool
	 */
	abstract public function rollback();

	/**
	 * Fetch one row
	 *
	 * @param mixed $result
	 * @param int $fetchType
	 * @return array|false|null
	 * Return false when failed.
	 * Return null when no result.
	 */
	abstract public function fetch($result, $fetchType);

	/**
	 * Free result
	 *
	 * @param mixed $result
	 */
	abstract public function free($result);

	/**
	 * Get last insert id
	 *
	 * @return int
	 */
	abstract public function insertId();

	abstract public function quote($string);

	abstract public function getErrorCode();

	abstract public function getErrorMessage();

	abstract public function fetchAll($query, $fetchType);

	public function getConnection()
	{
		return $this->conn;
	}

}