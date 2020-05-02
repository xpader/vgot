<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 17-4-23
 * Time: 下午4:52
 */
namespace vgot\Core;

use vgot\Exceptions\ApplicationException;
use vgot\Exceptions\ExitException;
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
 * @property \vgot\Web\Session $session
 * @property \vgot\Core\Security $security
 */
class Application
{

	/**
	 * Default providers
	 * @var array
	 */
	protected $_providers = [
		'db' => 'vgot\Database\DB::connection'
	];

	private static $instance;

	public function __construct($archPath)
	{
		self::$instance = $this;

		$this->config = new Config($archPath['config_path'], $archPath['common_config_path']);

		$this->_providers['view'] = [
			'class' => 'vgot\Core\View',
			'args' => [$archPath['views_path'], $archPath['common_views_path']]
		];

		//set config providers
		$providers = $this->config->get('providers');

		if (is_array($providers) && $providers) {
			$this->_providers = array_merge($this->_providers, $providers);
		}

		$this->input = new Input();
		$this->output = new Output();
		$this->router = new Router($archPath['controller_namespace']);
	}

	public function __get($name)
	{
		if (!isset($this->_providers[$name])) {
			throw new \ErrorException("Undefined application property '$name'.");
		}

		$provider = $this->_providers[$name];
		$class = $call = $args = $props = false;

		if (is_array($provider)) {
			if (isset($provider['class'])) {
				$class = $provider['class'];
			} elseif (isset($provider['callable'])) {
				$call = $provider['callable'];
			} else {
				throw new ApplicationException("Can not resolve provider '$name', provider must contain 'class' or 'callable' element.");
			}

			isset($provider['args']) && $args = $provider['args'];
			isset($provider['props']) && $props = $provider['props'];

		} elseif (is_string($provider) && class_exists($provider)) {
			$class = $provider;
		} elseif (is_callable($provider)) {
			$call = $provider;
		} elseif (is_object($provider)) {
			return $this->$name = $provider;
		} else {
			throw new ApplicationException("Can not resolve provider '$name'.");
		}

		if ($class) {
			$this->$name = $args ? (new \ReflectionClass($class))->newInstanceArgs($args) : new $class;

			if (is_array($props)) {
				foreach ($props as $k => $v) {
					$this->$name->$k = $v;
				}
			}
		} else {
			$this->$name = $args ? call_user_func_array($call, $args) : call_user_func($call);
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

		//call onBoot
		$onBoot = $this->config->get('on_boot');
		if (is_callable($onBoot)) {
			call_user_func($onBoot);
		}

		/**
		 * Invoke controller action
		 * @var $instance Controller
		 */
		try {
			$this->controller = $instance = new $uri['controller'];

			if ($instance instanceof Controller === false) {
				throw new ApplicationException($uri['controller'] . ' is not a controller class.');
			}

			$action = $this->router->findAction($instance, $uri['params']);


			if ($action === false) {
				throw new HttpNotFoundException();
			}

			$instance->__init();
			call_user_func_array([$instance, $action], $uri['params']);
		} catch (ExitException $e) {
			$message = $e->getMessage();
			if ($message) {
				echo $message;
			}
		}

		$this->output->flush();
	}

	/**
	 * Register provider
	 *
	 * @param string $name
	 * @param Object|string|callable $struct Object, class name to instance or callable to set return value.
	 * @throws ApplicationException
	 */
	public function register($name, $struct)
	{
		if (isset($this->$name)) {
			throw new ApplicationException("Register provider name '$name' is already exists in application.");
		}

		if (is_object($struct)) {
			$this->$name = $struct;
		} elseif (is_array($struct)) {
			if (!isset($struct['class']) && !isset($struct['callable'])) {
				throw new ApplicationException("Register provider '$name' must contain 'class' or 'callable' element.");
			}
		} elseif (!is_callable($struct, true)) {
			throw new ApplicationException("Unexpected register provider '$name' type '".gettype($struct)."'.");
		}

		$this->_providers[$name] = $struct;
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