<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/4/23
 * Time: 02:08
 */

namespace vgot\Core;


use vgot\Exceptions\ApplicationException;

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
	 * @param string $namespace Controller Namespace
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

		if (
			(is_callable([$instance, $action]) && $action != 'init')
			|| (($action = 'action'.ucfirst($action)) && is_callable([$instance, $action]))
		) {
			array_shift($params);
		} elseif (is_callable([$instance, '_redirect'])) {
			$action = '_redirect';
		} else {
			return false;
		}

		$this->uri['action'] = $action;
		$this->uri['params'] = $params;

		return $action;
	}

	public function camelCase($source)
	{
		return preg_replace_callback('/'.preg_quote($this->routes['case_symbol']).'([a-z])/', function($m) {
			return strtoupper($m[1]);
		}, $source);
	}

	/**
	 * Get request router uri
	 *
	 * @param string $key Specify to get: source, real, array, controller, params
	 * @return array|string
	 */
	public function getUri($key=null)
	{
		if ($key === null) {
			return $this->uri;
		} else {
			return isset($this->uri[$key]) ? $this->uri[$key] : null;
		}
	}

	/**
	 * Explain and translate the true uri
	 *
	 * @return array
	 * @throws ApplicationException
	 */
	protected function exportURI()
	{
		//export the request uri
		switch($this->routes['route_method']) {
			case 'PATH_INFO': $sourceUri = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : ''; break;
			case 'QUERY_STRING':
				if (!empty($_SERVER['QUERY_STRING'])) {
					$end = strpos($_SERVER['QUERY_STRING'], '&');
					$sourceUri = $end === false ? $_SERVER['QUERY_STRING'] : substr($_SERVER['QUERY_STRING'], 0, $end);
				} else {
					$sourceUri = '';
				}
				break;
			case 'GET':
				list($controller, $action) = $this->routes['route_params'];
				$controller = isset($_GET[$controller]) ? $_GET[$controller] : substr($this->routes['default_controller'], 0, -10);
				$action = isset($_GET[$action]) ? $_GET[$action] : $this->routes['default_action'];
				$sourceUri = $controller.'/'.$action;
				break;
			default: throw new ApplicationException('Unsupport route method: '.$this->routes['method']);
		}

		$sourceUri = $realUri = trim($sourceUri, '/');

		//Remove suffix from $realUri
		if ($this->routes['suffix']) {
			$suffix = preg_quote($this->routes['suffix']);
			$realUri = preg_replace("/$suffix$/", '', $realUri);
		}

		//translate routes
		if ($routes = $this->routes['route_maps']) {
			foreach ($routes as $exp => $route) {
				$exp = '#^'.$exp.'$#';
				if (preg_match($exp, $realUri)) {
					$realUri = preg_replace($exp, $route, $realUri);
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