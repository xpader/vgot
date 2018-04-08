<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/5/1
 * Time: 02:27
 */

namespace vgot\Core;

/**
 * Vgot Base
 *
 * @property Router $router
 * @property Config $config
 * @property Output $output
 * @property View $view
 */
abstract class Base
{

	private $application;

	public function __construct()
	{
		$this->application = Application::getInstance();
	}

	public function __get($name)
	{
		return $this->application->$name;
	}

}