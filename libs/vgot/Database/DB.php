<?php
/**
 * Created by PhpStorm.
 * User: Pader
 * Date: 2017/5/3
 * Time: 22:19
 */

namespace vgot\Database;


use vgot\Core\Application;
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
	 * @param string $mark
	 * @return Connection
	 * @throws DatabaseException
	 */
	public static function connection($mark='default')
	{
		if (!isset(self::$connections[$mark])) {
			$config = Application::getInstance()->config->get($mark, 'databases');

			if ($config === null) {
				throw new DatabaseException("No found database config '$mark'.");
			}

			$conn = new Connection($config);

			self::$connections[$mark] = $conn;
		}

		return self::$connections[$mark];
	}

}