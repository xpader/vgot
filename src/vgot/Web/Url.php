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

	public static function site()
	{
	}


}