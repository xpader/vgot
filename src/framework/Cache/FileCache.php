<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/7/15
 * Time: 01:18
 */
namespace vgot\Cache;

use vgot\Exceptions\ApplicationException;
use vgot\Utils\ArrayUtil;

/**
 * File for Cache
 * @package vgot\Cache\Driver
 */
class FileCache extends Cache {

	public $storDir;
	public $dirLevel = 0;
	public $gcProbability = 10; //0.001%

	/**
	 * Cache in current request process memory
	 * It's better performance for same key get in one request, but more memory used and not cache stable.
	 * @var bool
	 */
	public $cacheInMemory = false;

	protected $_cache = [];

	public function __construct($config)
	{
		configClass($this, $config);

		if ($this->storDir === null) {
			throw new ApplicationException('$storDir must be configure when using '.__CLASS__);
		}

		$this->dirLevel > 16 && $this->dirLevel = 16;
	}

	public function get($key, $defaultValue=null)
	{
		$file = $this->getFilename($key);

		if ($this->cacheInMemory && array_key_exists($key, $this->_cache)) {
			$data = $this->_cache[$key];
		} else {
			if (!is_file($file)) {
				return $defaultValue;
			}

			$data = @include $file;

			if (!is_array($data)) {
				$this->deleteFile($file);
				return $defaultValue;
			}
		}

		$now = time();

		if ($data['expired_at'] == 0 || $now < $data['expired_at']) {
			if ($this->cacheInMemory && !array_key_exists($key, $this->_cache)) {
				$this->_cache[$key] = $data;
			}
			return $data['value'];
		}

		$this->deleteFile($file);

		if ($this->cacheInMemory && array_key_exists($key, $this->_cache)) {
			unset($this->_cache[$key]);
		}

		return $defaultValue;
	}

	public function set($key, $value, $duration=0)
	{
		$file = $this->getFilename($key);
		$now = time();

		$data = [
			'key' => $key,
			'value' => $value,
			'expired_at' => $duration == 0 ? $duration : $now + $duration
		];

		$content = '<?php return '.ArrayUtil::export($data, true).';';

		if (!is_file($file)) {
			$dir = dirname($file);
			if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
				$error = error_get_last();
				throw new ApplicationException('Unable to create cache directory: '.$error['message']);
			}
		}

		if (@file_put_contents($file, $content, LOCK_EX) === false) {
			$error = error_get_last();
			throw new ApplicationException('Unable to write cache file: '.$error['message']);
		}

		$this->cacheInMemory && $this->_cache[$key] = $data;
		$this->deleteOpcache($file);
		$this->gc();

		return true;
	}

	public function delete($key)
	{
		$file = $this->getFilename($key);

		if (is_file($file) && !$this->deleteFile($file)) {
			return false;
		}

		if ($this->cacheInMemory && array_key_exists($key, $this->_cache)) {
			unset($this->_cache[$key]);
		}

		return true;
	}

	/**
	 * Garbage Collection
	 * Remove expired cache files.
	 *
	 * @param bool $force
	 */
	public function gc($force=false)
	{
		if (!$force && mt_rand(0, 1000000) >= $this->gcProbability) {
			return;
		}
		$now = time();
		$this->gcr($this->storDir, $now);
	}

	protected function gcr($path, $now) {
		if (($handle = opendir($path)) !== false) {
			while (($file = readdir($handle)) !== false) {
				if ($file == '.' || $file == '..' || substr($file, 0, 1) == '.') {
					continue;
				}

				$fullPath = $path . DIRECTORY_SEPARATOR . $file;

				if (is_dir($fullPath)) {
					$this->gcr($fullPath, $now);
					@rmdir($fullPath);
				} else {
					$data = @include $fullPath;
					if (!is_array($data) || ($data['expired_at'] > 0 && $data['expired_at'] < $now)) {
						unlink($fullPath);
						$this->deleteOpcache($fullPath);
					}
				}
			}
			closedir($handle);
		}
	}

	protected function getFilename($key)
	{
		$hash = md5($key);

		if ($this->dirLevel > 0) {
			$seg = 2 * $this->dirLevel;
			$prefix = str_split(substr($hash, 0, $seg), 2);
			$path = join(DIRECTORY_SEPARATOR, $prefix).DIRECTORY_SEPARATOR;
			if ($suffix = substr($hash, $seg)) {
				$path .= $suffix.'_';
			}
		} else {
			$path = $hash.'_';
		}

		$path .= str_replace(['?', '*', ' ', '$', '&', '\\', '/', '.'], '_', $key);

		if (strlen($path) > 128) {
			$path = substr($path, 0, 128);
		}

		return $this->storDir.DIRECTORY_SEPARATOR.$path.'.php';
	}

	protected function deleteFile($file)
	{
		$delete = unlink($file);
		$this->deleteOpcache($file);
		return $delete;
	}

	protected function deleteOpcache($file)
	{
		if (function_exists('opcache_is_script_cached') && opcache_is_script_cached($file)) {
			opcache_invalidate($file);
		}
	}

}