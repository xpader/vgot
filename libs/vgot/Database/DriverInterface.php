<?php
/**
 * Created by PhpStorm.
 * User: Pader
 * Date: 2017/5/3
 * Time: 22:51
 */

namespace vgot\Database;


abstract class DriverInterface {

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

	abstract public function getErrorCode();

	abstract public function getErrorMessage();

}