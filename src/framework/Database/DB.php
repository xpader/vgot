<?php
/**
 * Created by PhpStorm.
 * User: Pader
 * Date: 2017/5/3
 * Time: 22:19
 */

namespace vgot\Database;

use vgot\Core\Application;
use vgot\Exceptions\ApplicationException;
use vgot\Exceptions\DatabaseException;

class DB
{

	const FETCH_NUM = 0;
	const FETCH_ASSOC = 1;
	const FETCH_BOTH = 2;

	protected static $connections = [];

	/**
	 * Get Database Connection
	 *
	 * @param string $index
	 * @param bool $queryBuilder Use query builder mode
	 * @return Connection|QueryBuilder
	 * @throws DatabaseException
	 */
	public static function connection($index=null, $queryBuilder=null)
	{
		if ($index === null) {
			$index = Application::getInstance()->config->get('default_connection', 'databases');
		} elseif ($index == 'default_connection') {
			throw new DatabaseException("Unexcept database index '$index'.");
		}

		if (!isset(self::$connections[$index])) {
			$config = Application::getInstance()->config->get($index, 'databases');

			if ($config === null) {
				throw new DatabaseException("No found database config '$index'.");
			}

			if ($queryBuilder === null && isset($config['query_builder'])) {
				$queryBuilder = $config['query_builder'];
			}

			$conn = $queryBuilder ? new QueryBuilder($config) : new Connection($config);

			self::$connections[$index] = $conn;
		}

		if ($queryBuilder && self::$connections[$index] instanceof QueryBuilder === false) {
			throw new DatabaseException('Can not get QueryBuilder connection after none QueryBuilder instance.');
		}

		return self::$connections[$index];
	}

}