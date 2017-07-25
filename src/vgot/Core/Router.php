<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/4/23
 * Time: 02:08
 */

namespace vgot\Core;


class Router
{

	protected $routes;

	/**
	 * Controller Namespace
	 *
	 * @var string
	 */
	protected $namespace;

	protected $uri;

	/**
	 * Router constructor.
	 *
	 * @param string $ctrlNS Controller Namespace
	 */
	public function __construct($namespace)
	{
		$this->routes = Application::getInstance()->config->load('routes', false, true);
		$this->namespace = $namespace;
	}

	public function parse()
	{
		//Export the request uri
		$uri = $this->exportURI();

		//Find controller
		$controller = '';
		$path = $this->namespace;
		$params = array();

		$arr = $this->routes['case_symbol'] ? $this->camelCase($uri['array']) : $uri['array'];

		foreach ($arr as $i => $segment) {
			$name = ucfirst($segment);
			$className = $path.'\\'.$name.'Controller';

			if (class_exists($className)) {
				$controller = $className;
				$params = array_slice($uri['array'], $i+1);
				break;
			}

			$path .= '\\'.($this->routes['ucfirst'] ? $name : $segment);
		}

		if ($controller == '' && class_exists($path.'\\'.$this->routes['default_controller'])) {
			$controller = $path.'\\'.$this->routes['default_controller'];
		}

		if ($controller == '') {
			return false;
		}

		$uri['controller'] = $controller;
		$uri['params'] = $params;

		$this->uri = $uri;

		return $uri;
	}

	/**
	 * Find action function name for request
	 *
	 * @param Controller $instance
	 * @param array $params
	 * @return string|false
	 */
	public function findAction($instance, &$params)
	{
		$action = !empty($params[0]) ? $params[0] : $this->routes['default_action'];

		if ($this->routes['case_symbol']) {
			$action = $this->camelCase($action);
		}

		if (is_callable([$instance, $action])
			|| (($action = 'action'.ucfirst($action)) && is_callable([$instance, $action]))) {
			unset($params[0]);
		} elseif (is_callable([$instance, '_redirect'])) {
			$action = '_redirect';
		} else {
			return false;
		}

		return $action;
	}

	public function camelCase($source)
	{
		return preg_replace_callback('/'.preg_quote($this->routes['case_symbol']).'([a-z])/', function($m) {
			return strtoupper($m[1]);
		}, $source);
	}

	/**
	 * Explain and translate the true uri
	 *
	 * @return mixed|string
	 */
	protected function exportURI() {
		//export the request uri
		$sourceUri = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : '';

		//translate routes
		$realUri = $sourceUri;

		if ($routes = $this->routes['route_maps']) {
			foreach ($routes as $exp => $route) {
				$exp = '#^'.$exp.'$#';
				if (preg_match($exp, $sourceUri)) {
					$realUri = preg_replace($exp, $route, $sourceUri);
					break;
				} elseif ($sourceUri == $exp) {
					break;
				}
			}
		}

		return array(
			'source' => $sourceUri,
			'real' => $realUri,
			'array' => $realUri ? explode('/', $realUri) : array()
		);
	}

}