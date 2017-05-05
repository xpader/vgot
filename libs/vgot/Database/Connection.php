<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/5/1
 * Time: 04:04
 */

namespace vgot\Database;


use vgot\Exceptions\DatabaseException;

/**
 * Datanase Connection
 *
 * @package vgot\Database
 * @method getConnection() Get Driver Connection Base Object
 */
class Connection
{

	/**
	 * Database Driver Instance
	 *
	 * @var DriverInterface
	 */
	protected $di;

	protected $config;

	protected $lastQuery = null;

	protected $queryRecords = [];

	public function __construct($config)
	{
		$driverClass = '\vgot\Database\Driver\\'.ucfirst($config['driver']).'Driver';

		if (!class_exists($driverClass)) {
			throw new DatabaseException("Unsupport database driver '{$config['driver']}'.");
		}

		$this->config = $config;

		$this->di = new $driverClass();
		$this->connect();
	}

	public function connect()
	{
		if (!$this->di->connect($this->config)) {
			switch ($this->config['type']) {
				case 'mysql': $server = 'MySQL Server'; break;
				case 'sqlserv': $server = 'SQLServer'; break;
				case 'sqlite': $server = 'SQLite'; break;
				default:
					$server = ucfirst($this->config['type']).' Server';
			}

			throw new DatabaseException("Failed to connect to $server", $this->di);
		}
	}

	public function prepare($sql, $params=[])
	{}

	/**
	 * Make A Query
	 *
	 * @param string $sql
	 * @param array $params
	 * @return self
	 * @throws DatabaseException
	 */
	public function query($sql, $params=[])
	{
		//debug
		if (!empty($this->config['debug'])) {
			$qst = array_sum(explode(' ', microtime()));
		}

		$query = $this->di->query($sql);

		if (isset($qst)) {
			$qet = array_sum(explode(' ', microtime()));
			$queryTime = round(($qet - $qst), 6);
			$this->queryRecords[] = ['sql'=>$sql,'used'=>$queryTime];
		}

		if (!$query) {
			throw new DatabaseException("Query error", $this->di, $sql);
		}

		$this->lastQuery = $query;

		return $this;
	}

	/**
	 * Fetch one row from query result
	 *
	 * @param int $fetchType
	 * @return array|null
	 * @throws DatabaseException
	 */
	public function fetch($fetchType=DB::FETCH_ASSOC)
	{
		$result = $this->di->fetch($this->lastQuery, $fetchType);

		if ($result === false) {
			throw new DatabaseException("Fetch not a query result.");
		}

		return $result;
	}

	/**
	 * Fetch all rows from query result
	 *
	 * @param int $fetchType
	 * @return array
	 */
	public function fetchAll($fetchType=DB::FETCH_ASSOC)
	{
		return $this->di->fetchAll($this->lastQuery, $fetchType);
	}

	/**
	 * Fetch a column value in first result row
	 *
	 * @param int|string $col
	 * @return mixed|bool|null
	 */
	public function fetchColumn($col=0)
	{
		return $this->di->fetchColumn($this->lastQuery, $col, is_numeric($col) ? DB::FETCH_NUM : DB::FETCH_ASSOC);
	}

	/**
	 * Close database connection
	 */
	public function close()
	{
		$this->lastQuery = null;
		$this->di && $this->di->close();
	}

	public function __call($name, $args)
	{
		if (is_callable([$this->di, $name])) {
			return call_user_func_array([$this->di, $name], $args);
		} else {
			throw new \ErrorException("Call to undefined method ".__CLASS__."::$name()");
		}
	}

	public function __destruct() {
		$this->close();
	}

}