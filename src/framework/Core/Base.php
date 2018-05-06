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
 * @property Config $config
 * @property Input $input
 * @property Output $output
 * @property Router $router
 * @property View $view
 * @property \vgot\Database\Connection|\vgot\Database\QueryBuilder $db
 * @property \vgot\Cache\Cache $cache
 * @property \vgot\Web\Session $session
 * @property \vgot\Core\Security $security
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