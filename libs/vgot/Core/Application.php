<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 17-4-23
 * Time: 下午4:52
 */

namespace vgot\Core;

/**
 * Vgot Application
 *
 * @property Router $router
 * @property Config $config
 * @property Controller $controller
 */
class Application
{

	protected $router;
	protected $config;
	protected $controller;
	protected $db;

	public function __get($name)
	{
		return $this->$name;
	}

	/**
	 * Protect system core not being rewritten
	 *
	 * The system core can be only write once.
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		if (!isset($this->$name) || $this->$name === null) {
			$this->$name = $value;
		} else {
			trigger_error("Uncaught Error: Cannot access protected property \\vgot\\Core\\Application::\${$name}", E_USER_WARNING);
		}
	}

}