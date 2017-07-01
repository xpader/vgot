<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 17-4-23
 * Time: 下午4:52
 */
namespace vgot\Core;

use vgot\Database\DB;
use vgot\Exceptions\ApplicationException;
use vgot\Exceptions\HttpNotFoundException;

/**
 * Vgot Application
 *
 * @property Router $router
 * @property Config $config
 * @property Output $output
 * @property View $view
 * @property Controller $controller
 * @property \vgot\Database\Connection|\vgot\Database\QueryBuilder $db
 */
class Application
{

	protected $config;
	protected $router;
	protected $output;
	protected $view;
	protected $controller;
	protected $db;

	private static $instance;

	public function __construct($archPath)
	{
		self::$instance = $this;

		$this->config = new Config($archPath['config_path'], $archPath['common_config_path']);
		$this->config->load('application');

		$this->output = new Output();
		$this->view = new View($archPath['views_path'], $archPath['common_views_path']);
		$this->router = new Router($archPath['controller_namespace']);
	}

	public function __get($name)
	{
		if ($this->$name !== null) {
			return $this->$name;
		}

		switch ($name) {
			case 'db':
				if ($this->db === null) {
					$this->db = DB::connection();
				}
				break;
		}

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
		if (!isset($this->$name)) {
			$this->$name = $value;
		} else {
			trigger_error("Uncaught Error: Cannot access protected property \\vgot\\Core\\Application::\${$name}", E_USER_WARNING);
		}
	}

	/**
	 * 执行应用
	 *
	 * @throws HttpNotFoundException
	 */
	public function execute()
	{
		if ($this->controller !== null) {
			return;
		}

		$uri = $this->router->parse();

		//Controller not found
		if ($uri === false) {
			throw new HttpNotFoundException();
		}

		$this->controller = $instance = new $uri['controller'];

		$action = !empty($uri['params'][0]) ? $uri['params'][0] : $this->config->get('default_action');

		if ($this->config->get('case_symbol')) {
			$action = $this->router->symbolConvert($action);
		}

		if (is_callable([$instance, $action])
			|| ($action = 'action'.ucfirst($action) && is_callable([$instance, $action]))) {
			unset($uri['params'][0]);
		} elseif (is_callable([$instance, '_redirect'])) {
			$action = '_redirect';
		} else { //Action not found
			throw new HttpNotFoundException();
		}

		call_user_func_array([$instance, $action], $uri['params']);

		$this->output->flush();
	}

	/**
	 * 向实例中注册对象
	 *
	 * @param $name
	 * @param $object
	 * @throws ApplicationException
	 */
	public function register($name, $object)
	{
		if (isset($this->$name)) {
			throw new ApplicationException("Can not register object because name '$name' exists in instance.");
		}

		$this->$name = $object;
	}

	/**
	 * 获取应用实例
	 *
	 * @return Application
	 */
	public static function getInstance()
	{
		return self::$instance;
	}

}