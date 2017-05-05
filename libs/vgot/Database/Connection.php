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
 * Class Connection
 * @package vgot\Database
 * @method close() Close database connection
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
			$this->queryRecords[] = array('sql'=>$SQL,'used'=>$queryTime);
		}

		if (!$query) {
			throw new DatabaseException("Query error", $this->di, $sql);
		}

		return $query;
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
		$this->di->close();
	}

}