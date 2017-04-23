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
	protected $ctrlNS;

	/**
	 * Router constructor.
	 *
	 * @param string $ctrlNS Controller Namespace
	 */
	public function __construct($ctrlNS)
	{
		$this->routes = \vgot\app()->config->load('routes', false, true);
		$this->ctrlNS = $ctrlNS;
	}

	public function parse()
	{
		//Export the request uri
		$uri = $this->exportURI();

		//Find controller
		$controller = '';
		$path = $this->ctrlNS;
		$params = array();

		$arr = $this->routes['case_symbol'] ? $this->symbolConvert($uri['array']) : $uri['array'];

		if ($this->routes['ucfirst']) {
			$arr = array_map('ucfirst', $arr);
		}

		foreach ($arr as $i => $segment) {
			$path .= '\\'.$segment;
			$className = $path.'Controller';

			if (class_exists($className)) {
				$controller = $className;
				$params = array_slice($uri['array'], $i+1);
				break;
			}
		}

		if ($controller == '' && class_exists($path.'\\'.$this->routes['default_controller'])) {
			$controller = $path.'\\'.$this->routes['default_controller'];
		}

		if ($controller == '') {
			return false;
		}

		$uri['controller'] = $controller;
		$uri['params'] = $params;

		return $uri;
	}

	public function symbolConvert($source)
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