<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/7/24
 * Time: 01:17
 */
namespace vgot\Web;

use vgot\Core\Application;
use vgot\Exceptions\ApplicationException;

/**
 * Class Session
 * @package vgot\Web
 */
class Session
{

	public $lifetime;

	/**
	 * Sessin handler
	 *
	 * internal: php
	 * custom for: cache, db
	 * @var string
	 */
	public $handler = 'php';
	public $savePath;
	public $name;
	public $cookiePath = '/';
	public $cookieDomain;
	public $cookieSecure = false;
	public $cookieHttponly = false;

	protected $started = false;

	public function __construct($config=[])
	{
		configClass($this, $config);

		$app = Application::getInstance();

		//if no set lifetime, use default session.gc_maxlifetime in php.ini
		$lifetime = (int)@ini_get('session.gc_maxlifetime');
		!$this->lifetime && $this->lifetime = $lifetime ?: 86400;
		$this->lifetime != $lifetime && ini_set('session.gc_maxlifetime', $this->lifetime);

		$app->config->set('session_maxlifetime', $this->lifetime);

		//save path and name
		$this->savePath && session_save_path($this->savePath);
		$this->name && session_name($this->name);

		session_set_cookie_params($this->lifetime, $this->cookiePath, $this->cookieDomain, $this->cookieSecure, $this->cookieHttponly);

		//set handler
		if ($this->handler != 'php') {
			$handlerClass = 'vgot\Web\SessionHandler\\'.ucfirst($this->handler).'Handler';

			if (!class_exists($handlerClass)) {
				throw new ApplicationException("Undefined session handler: {$this->handler}");
			}

			session_set_save_handler(new $handlerClass(), true);
		}

		$this->start();
	}

	public function start()
	{
		if (!$this->started) {
			session_start();
			$this->started = true;
		}
	}

	public function get($key, $defaultValue=null)
	{
		return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $defaultValue;
	}

	public function set($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	public function delete($key)
	{
		unset($_SESSION[$key]);
	}

}