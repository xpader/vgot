<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/8/2
 * Time: 00:48
 */

namespace vgot\Web;

use vgot\Core\Application;

class Url
{

	/**
	 * Return Base URL Fron Framework Configuration
	 *
	 * If base_url is empty in config, program will automatically identify and returns a relative base url address
	 *
	 * @param bool $absolute
	 * @return string
	 */
	public static function base($absolute=false)
	{
		static $baseUrl = null, $absoluteUrl = null;

		if ($absolute) {
			if ($absoluteUrl === null) {
				$absoluteUrl = self::base();
				if (!preg_match('!^\w+://!i', $absoluteUrl)) {
					$protocol = strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/')));
					//$port = $_SERVER['SERVER_PORT'] == 80 ? '' : ':'.$_SERVER['SERVER_PORT'];
					$absoluteUrl = $protocol.'://'.$_SERVER['HTTP_HOST'].$absoluteUrl;
				}
			}

			return $absoluteUrl;

		} else {
			if ($baseUrl === null) {
				$app = Application::getInstance();
				$baseUrl = $app->config->get('base_url');

				if ($baseUrl == '') {
					$baseUrl = str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']));
					$baseUrl = trim($baseUrl,'/');
					$baseUrl = empty($baseUrl) ? '/' : "/$baseUrl/";
				}
			}

			return $baseUrl;
		}
	}

	/**
	 * Generate a site url
	 *
	 * @param string|array $uri
	 * Just uri: 'tommy/jimmy/action'
	 * Uri with params: ['tommy/jimmy/action', 'param1'=>'value', 'param2'=>'value']
	 * @param bool $absolute
	 * @param bool $suffix
	 * @return string
	 */
	public static function site($uri, $absolute=false, $suffix=true)
	{
		$app = Application::getInstance();
		$entry = $app->config->get('entry_file');
		$url = self::base($absolute).$entry;

		if (is_array($uri)) {
			if (isset($uri[0])) {
				$tmp = array_shift($uri);
				$uri && $params = $uri;
				$uri = $tmp;
			} else {
				$uri = '';
				$uri && $params = $uri;
			}
		}

		switch ($app->config->get('route_method')) {
			case 'PATH_INFO':
				$join = $entry ? '/' : '';
				$uri && $url .= $join.$uri.($suffix ? $app->config->get('suffix') : '');
				break;
			case 'QUERY_STRING':
				$uri && $url .= '?'.$uri.($suffix ? $app->config->get('suffix') : '');
				break;
			case 'GET':
			default:
				$route = $app->config->get('route_param');
				$url .= "?$route=$uri";
		}

		if (isset($params)) {
			is_array($params) && $params = http_build_query($params);
			$url .= (strrpos($url, '?') !== false ? '&' : '?').$params;
		}

		return $url;
	}

	/**
	 * Get current url
	 *
	 * @param bool $absolute
	 * @return string
	 */
	public static function current($absolute=false)
	{
		if ($absolute) {
			$protocol = strtolower($_SERVER['SERVER_PROTOCOL']);
			$protocol = substr($protocol, 0, strpos($protocol, '/'));
			$port = $_SERVER['SERVER_PORT'];
			$port = $port == 80 ? '' : ':' . $port;
			$base = $protocol . '://' . $_SERVER['SERVER_NAME'] . $port;
		} else {
			$base = '';
		}

		return ($absolute ? $base : '') . $_SERVER['REQUEST_URI'];
	}

	/**
	 * Header Redirect
	 *
	 * Fast to goto an url
	 *
	 * @param string $uri
	 * @param string $method location or refresh
	 * @param int $httpResponseCode
	 * @return void
	 */
	public static function redirect($uri='', $method='location', $httpResponseCode=302) {
		if (!preg_match('#^https?://#i', $uri)) {
			$uri = self::site($uri);
		}

		switch($method) {
			case 'refresh': header('Refresh:0;url='.$uri); break;
			default: header('Location: '.$uri, TRUE, $httpResponseCode);
		}

		exit;
	}

}