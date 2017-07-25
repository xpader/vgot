<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 17-4-23
 * Time: 下午4:52
 */
namespace vgot\Core;

use vgot\Exceptions\ApplicationException;
use vgot\Exceptions\HttpNotFoundException;

/**
 * Vgot Application
 *
 * @property Config $config
 * @property Input $input
 * @property Output $output
 * @property Router $router
 * @property View $view
 * @property Controller $controller
 * @property \vgot\Database\Connection|\vgot\Database\QueryBuilder $db
 * @property \vgot\Cache\Cache $cache
 */
class Application
{

	/**
	 * Default providers
	 * @var array
	 */
	protected $_define = [
		'db' => 'vgot\Database\DB::connection'
	];

	private static $instance;

	public function __construct($archPath)
	{
		self::$instance = $this;

		$this->config = new Config($archPath['config_path'], $archPath['common_config_path']);
		$this->input = new Input();
		$this->output = new Output();
		$this->router = new Router($archPath['controller_namespace']);

		$this->_define['view'] = [
			'vgot\Core\View',
			[$archPath['views_path'], $archPath['common_views_path']]
		];
	}

	public function __get($name)
	{
		if (!isset($this->_define[$name])) {
			throw new \ErrorException("Undefined application property '$name'.");
		}

		$def = $this->_define[$name];

		if (is_array($def)) {
			$call = $def[0];
			$args = isset($def[1]) ? $def[1] : null;
		} else {
			$call = $def;
			$args = null;
		}

		if (is_string($call) && class_exists($call)) {
			$this->$name = $args === null ? new $call() : (new \ReflectionClass($call))->newInstanceArgs($args);
		} elseif (is_callable($call)) {
			$this->$name = $args === null ? call_user_func($call) : call_user_func_array($call, $args);
		} else {
			throw new ApplicationException("Wrong format registered object '$name' to call.");
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
	//public function __set($name, $value)
	//{
	//	if (!isset($this->$name)) {
	//		$this->$name = $value;
	//	} else {
	//		trigger_error("Uncaught Error: Cannot access protected property \\vgot\\Core\\Application::\${$name}", E_USER_WARNING);
	//	}
	//}

	/**
	 * 执行应用
	 *
	 * @throws HttpNotFoundException
	 */
	public function execute()
	{
		if (isset($this->controller)) {
			return;
		}

		$uri = $this->router->parse();

		//Controller not found
		if ($uri === false) {
			throw new HttpNotFoundException();
		}

		//set config providers
		$providers = $this->config->get('providers');

		if (is_array($providers) && $providers) {
			$this->_define = array_merge($this->_define, $providers);
		}

		//Invoke controller action
		$this->controller = $instance = new $uri['controller'];
		$action = $this->router->findAction($instance, $uri['params']);

		if ($action === false) {
			throw new HttpNotFoundException();
		}

		call_user_func_array([$instance, $action], $uri['params']);

		$this->output->flush();
	}

	/**
	 * Register object to application
	 *
	 * @param string $name
	 * @param Object|string|array $object Object, class name to instance or callable to set return value.
	 * @param array $args Arguments for instance class or callable.
	 * @throws ApplicationException
	 */
	public function register($name, $object, $args=null)
	{
		if (isset($this->$name)) {
			throw new ApplicationException("Can not register object because name '$name' exists in instance.");
		}

		if (is_object($object)) {
			$this->$name = $object;
		} elseif ($args === null) {
			$this->_define[$name] = is_array($object) ? [$object] : $object;
		} else {
			$this->_define[$name] = [$object, $args];
		}
	}

	/**
	 * Get application instance
	 *
	 * @return Application
	 */
	public static function getInstance()
	{
		return self::$instance;
	}

}