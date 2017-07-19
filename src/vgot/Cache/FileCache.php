<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/7/15
 * Time: 01:18
 */
namespace vgot\Cache;

use vgot\Exceptions\ApplicationException;

/**
 * File Cache Driver
 * @package vgot\Cache\Driver
 */
class FileCache implements CacheInterface {

	public $storDir;
	public $dirLevel = 0;

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
	}

	public function get($key, $defaultValue=null)
	{
		$file = $this->getFilename($key);

		if ($this->cacheInMemory && isset($this->_cache[$key])) {
			$data = $this->_cache[$key];
		} else {
			if (!is_file($file)) {
				return $defaultValue;
			}

			$data = include $file;

			if (!is_array($data)) {
				return $defaultValue;
			}
		}

		$now = time();

		if ($data['expired_at'] == 0 || $now < $data['expired_at']) {
			if ($this->cacheInMemory && !isset($this->_cache[$key])) {
				$this->_cache[$key] = $data;
			}

			return $data['value'];
		}

		@unlink($file);

		if ($this->cacheInMemory && isset($this->_cache[$key])) {
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

		$content = '<?php return '.$this->varExport($data).';';

		if (!is_file($file)) {
			$dir = dirname($file);
			if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
				throw new ApplicationException('Set cache failed, create dir error: '.error_get_last());
			}
		}

		if (file_put_contents($file, $content, LOCK_EX) !== false) {
			if ($this->cacheInMemory) {
				$this->_cache[$key] = $data;
			}

			return true;
		} else {
			return false;
		}
	}

	public function delete($key)
	{
		$file = $this->getFilename($key);

		if (is_file($file) && !unlink($file)) {
			return false;
		}

		if ($this->cacheInMemory && isset($this->_cache[$key])) {
			unset($this->_cache[$key]);
		}

		return true;
	}

	protected function getFilename($key)
	{
		$hash = md5($key);
		$path = '';

		if ($this->dirLevel > 0) {
			$seg = 2 * $this->dirLevel;
			$prefix = str_split(substr($hash, 0, $seg), 2);
			$path .= join(DIRECTORY_SEPARATOR, $prefix).DIRECTORY_SEPARATOR.substr($hash, $seg);
		}

		$key = preg_replace('/[\?\*\s\$&\\\\\/\.]/', '_', $key);
		$path .= '_'.$key;

		if (strlen($path) > 60) {
			$path = substr($path, 0, 60);
		}

		return $this->storDir.DIRECTORY_SEPARATOR.$path.'.php';
	}

	private function varExport($var, $level=0)
	{
		switch (gettype($var)) {
			case 'array':
				$tabEnd = str_repeat("\t", $level);
				$tab = $tabEnd."\t";
				$code = "[\r\n";

				foreach ($var as $key => $val) {
					is_string($key) && $key = "'$key'";
					if (is_array($val)) {
						$code .= $tab.$key.' => '.$this->varExport($val, $level + 1);
					} else {
						$code .= $tab.$key.' => '.$this->varExport($val);
					}
					$code .= ",\r\n";
				}

				$var && $code = substr_replace($code, '', -3, 1);
				$code .= "$tabEnd]";
				break;

			case 'string':
				$code = '\''.addcslashes($var,'\\\'').'\'';
				break;

			default:
				$code = $var;
		}

		return $code;
	}

}