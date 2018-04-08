<?php
/**
 * Created by PhpStorm.
 * User: Pader
 * Date: 2017/5/3
 * Time: 22:29
 */

namespace vgot\Database\Driver;

use PDO;
use PDOStatement;
use vgot\Database\DB;
use vgot\Database\DriverInterface;
use vgot\Exceptions\DatabaseException;

/**
 * Pdo Database Driver
 *
 * @package vgot\Database\Driver
 * @property PDO $conn
 */
class PdoDriver extends DriverInterface {

	/* @var \PDOException */
	protected $ex;

	public function connect($config)
	{
		if (!isset($config['type'])) {
			throw new DatabaseException('Must specify \'type\' when using PDO driver!');
		}

		$opt = array();

		//firebird,mssql,mysql,oci,oci8,odbc,pgsql,sqlite
		switch ($config['type']) {
			case 'mysql':
				$dsnp = array();

				$dsnp['host'] = $config['host'];
				$dsnp['dbname'] = $config['database'];

				if (isset($config['port'])) $dsnp['port'] = $config['port'];

				if (!empty($config['charset'])) {
					$dsnp['charset'] = $config['charset'];
					if ($config['type'] == 'mysql') {
						$set = "SET NAMES '{$config['charset']}'";
						empty($config['collate']) || $set .= " COLLATE '{$config['collate']}'";
						$opt[PDO::MYSQL_ATTR_INIT_COMMAND] = $set;
					}
				}

				$dsn = array();

				foreach ($dsnp as $k => $v) {
					$dsn[] = $k.'='.$v;
				}

				$dsn = $config['type'].':'.join(';',$dsn);
				$args = array($dsn, $config['username'], $config['password']);
				break;

			case 'sqlite':
				$dsn = "sqlite:{$config['filename']}";
				$args = array($dsn);
				break;
			default:
				throw new DatabaseException('Unable to connect database', "Not yet supported database type: '{$config['type']}'");
		}

		//Attribute setting
		!empty($config['pconnect']) && $opt[PDO::ATTR_PERSISTENT] = TRUE;
		isset($config['timeout']) && $opt[PDO::ATTR_TIMEOUT] = $config['timeout'];

		if ($opt) {
			!isset($args[1]) && $args[1] = null;
			!isset($args[2]) && $args[2] = null;
			$args[3] = $opt;
		}

		$this->type = $config['type'];

		//Connect
		try {
			$class = new \ReflectionClass('PDO');
			$this->conn = $class->newInstanceArgs($args);
		} catch (\PDOException $e) {
			$this->ex = $e;
			return false;
		}

		return true;
	}

	public function close()
	{
		if ($this->conn !== null) {
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

	public function query($sql)
	{
		return $this->conn->query($sql);
	}

	public function exec($sql)
	{
		return $this->conn->exec($sql);
	}

	public function beginTransaction()
	{
		return $this->conn->beginTransaction();
	}

	public function commit()
	{
		return $this->conn->commit();
	}

	public function rollback()
	{
		return $this->conn->rollBack();
	}

	public function fetch($query, $fetchType=DB::FETCH_ASSOC)
	{
		if (!($query instanceof PDOStatement)) {
			return false;
		}

		$fetchType = $this->getFetchType($fetchType);
		return $query->fetch($fetchType);
	}

	public function fetchAll($query, $fetchType=DB::FETCH_ASSOC)
	{
		if (!($query instanceof PDOStatement)) {
			return false;
		}

		$fetchType = $this->getFetchType($fetchType);
		return $query->fetchAll($fetchType);
	}

	public function insertId()
	{
		return $this->conn->lastInsertId();
	}

	public function quote($string)
	{
		return $this->conn->quote($string);
	}

	protected function getFetchType($fetchType)
	{
		switch ($fetchType) {
			case DB::FETCH_ASSOC: return PDO::FETCH_ASSOC; break;
			case DB::FETCH_NUM: return PDO::FETCH_NUM; break;
			case DB::FETCH_BOTH: return PDO::FETCH_BOTH; break;
			default: return PDO::FETCH_ASSOC;;
		}
	}

}