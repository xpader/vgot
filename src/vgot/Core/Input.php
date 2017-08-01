<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/5/1
 * Time: 03:15
 */

namespace vgot\Core;


use vgot\Exceptions\ApplicationException;

class Input
{

	/**
	 * Get Input Data
	 *
	 * Get request data and you can use function to filter data
	 *
	 * @param string $name
	 * @param mixed $defaultValue
	 * @param string|callable $filter Use function to filter request, eg: 'trim|html2text|stripslashes'
	 * @return mixed Filtered request content
	 */
	public function get($name, $defaultValue=null, $filter=null)
	{
		return $this->fetchVar($_GET, $name, $defaultValue, $filter);
	}

	public function post($name, $defaultValue=null, $filter=null)
	{
		return $this->fetchVar($_POST, $name, $defaultValue, $filter);
	}

	public function cookie($name, $defaultValue=null, $filter=null)
	{
		return $this->fetchVar($_COOKIE, $name, $defaultValue, $filter);
	}

	public function server($name, $defaultValue=null, $filter=null)
	{
		return $this->fetchVar($_SERVER, $name, $defaultValue, $filter);
	}

	public function request($name, $defaultValue=null, $filter=null)
	{
		return $this->fetchVar($_REQUEST, $name, $defaultValue, $filter);
	}


	/**
	 * Get Input Data From GET or Post
	 *
	 * It will fetch GET first, if none set int GET, then fetch POST
	 *
	 * @param string $name
	 * @param mixed $defaultValue
	 * @param string|callable $filter
	 * @return mixed
	 */
	public function gp($name, $defaultValue=null, $filter=null) {
		return isset($_GET[$name]) ? $this->fetchVar($_GET, $name, $defaultValue, $filter)
			: $this->fetchVar($_POST, $name, $defaultValue, $filter);
	}

	/**
	 * Get URI segment value
	 *
	 * @param int $number Which segment
	 * @return string
	 */
	public function segment($number)
	{
		$uri = $this->uri('array');
		return isset($uri[$number]) ? $uri[$number] : null;
	}

	/**
	 * Get params list
	 *
	 * Cas use for list() to take params elements as an variable.
	 * eg: list($id,$page,$style) = $this->input->params(3);
	 * Use function action($id='',$page='',$style='') is bad, because you need to set default value else
	 * php will unexcepted error
	 *
	 * @param int $length Return array length, 0 for all. when elements is not enough, the array will been padded null
	 * element to full length.
	 * @return array Params
	 */
	public function params($length=0)
	{
		$params = $this->uri('params');

		if ($length == 0) {
			return $params;
		} else {
			if (isset($params[$length - 1])) {
				return $params;
			} else {
				return array_pad($params, $length, null);
			}
		}
	}

	/**
	 * Get from uri like name/value/name/value as an array
	 *
	 * @param bool $pos Which segment start, else started after action
	 * @return array URI Assoc Data
	 */
	public function assoc($pos=null)
	{
		if ($pos === null) {
			$params = $this->uri('params');
		} else {
			$params = $pos > 1 ? array_slice($this->uri('array'),$pos-1) : $this->uri('array');
		}

		$assoc = array();
		foreach (array_chunk($params,2) as $row) {
			$assoc[$row[0]] = isset($row[1]) ? $row[1] : null;
		}

		return $assoc;
	}

	/**
	 * Get URI Parameter
	 *
	 * Specify to get: source, real, array, controller, params
	 *
	 * @param string $key
	 * @return string|array
	 */
	public function uri($key=null)
	{
		return Application::getInstance()->router->getUri($key);
	}

	/**
	 * Get IP Address
	 *
	 * @return string
	 */
	public function clientIp()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) $ip = $this->server('HTTP_CLIENT_IP');
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip = $this->server('HTTP_X_FORWARDED_FOR');
		elseif (!empty($_SERVER['REMOTE_ADDR'])) $ip = $this->server('REMOTE_ADDR');
		else $ip = '';

		preg_match('/[\d\.]{7,15}/', $ip, $ips);
		$ip = !empty($ips[0]) ? $ips[0] : 'unknown';

		return $ip;
	}

	/**
	 * Fetch value from GPCS vars, and apply filters.
	 *
	 * @param array $gpcs
	 * @param string $key
	 * @param mixed $defaultValue
	 * @param array|string $filter
	 * @return mixed
	 * @throws
	 */
	public function fetchVar(&$gpcs, $key, $defaultValue=null, $filter=null)
	{
		if (!isset($gpcs[$key])) return $defaultValue;

		$var = $gpcs[$key];

		if (!$filter) return $var;

		if (!is_array($filter)) {
			$filter = explode('|', $filter);
		}

		foreach ($filter as $func) {
			if (is_callable($func)) {
				$var = $func($var);
			} elseif (method_exists($this, 'filter'.ucfirst($func))) {
				$var = $this->{'filter'.ucfirst($func)}($var);
			} else {
				throw new ApplicationException("Undefined filter '$filter' for input!");
			}
		}

		return $var;
	}

	/**
	 * Get text from html, remove tags.
	 *
	 * @param string $str HTML code
	 * @return string
	 */
	public function filterHtml2text($str)
	{
		$str = preg_replace('/<sty(.*)\\/style>|<scr(.*)\\/script>|<!--(.*)-->/isU', '', $str);
		$str = str_replace(array('<br />','<br>','<br/>'), "\n", $str);
		$str = strip_tags($str);
		return $str;
	}

}