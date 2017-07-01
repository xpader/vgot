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
	 * @throws DatabaseException|ApplicationException
	 */
	public static function connection($index=null, $queryBuilder=null)
	{
		if ($index === null) {
			$index = Application::getInstance()->config->get('default_connection', 'databases');
		} elseif ($index == 'default_connection') {
			throw new ApplicationException('Not allow use system index \'default_connection\' to conneciton database.');
		}

		if (!isset(self::$connections[$index])) {
			$config = Application::getInstance()->config->get($index, 'databases');

			if ($config === null) {
				throw new DatabaseException("No found database config '$index'.");
			}

			if ($queryBuilder === null && isset($config['query_builder'])) {
				$queryBuilder = $config['query_builder'];
			}

			$conn = $queryBuilder ? new QueryBuilder($config) :  new Connection($config);

			self::$connections[$index] = $conn;
		}

		return self::$connections[$index];
	}

}