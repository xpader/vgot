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
		$routes = [];

		if ($routes) {
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