<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2018/4/21
 * Time: 19:32
 */
namespace vgot\Web\SessionHandler;

use vgot\Cache\Cache;
use vgot\Core\Application;
use vgot\Exceptions\ApplicationException;

/**
 * vgot Session Cache Handler
 * @package vgot\Web\SessionHandler
 */
class CacheHandler implements \SessionHandlerInterface
{

	/**
	 * @var \vgot\Cache\Cache
	 */
	protected $cache;
	public $prefix = 'sess:';

	public function close()
	{
		return true;
	}

	public function destroy($sid)
	{
		$this->cache->delete($this->prefix.$sid);
		return true;
	}

	public function gc($maxLifeTime)
	{
		return true;
	}

	/**
	 * @param string $savePath cache provider/prefix
	 * @param string $name
	 * @return bool
	 * @throws ApplicationException
	 */
	public function open($savePath, $name)
	{
		list($provider, $prefix) = explode('/', $savePath);
		$this->cache = Application::getInstance()->$provider;

		if (!($this->cache instanceof Cache)) {
			throw new ApplicationException('Cache session handler must implements of '.Cache::class.'.');
		}
		
		$this->prefix = $prefix;

		return true;
	}

	public function read($sid)
	{
		$data = $this->cache->get($this->prefix.$sid);
		return $data ?: '';
	}

	public function write($sid, $sessionData)
	{
		$lifetime = Application::getInstance()->config->get('session_maxlifetime');
		return $this->cache->set($this->prefix.$sid, $sessionData, $lifetime);
	}

}