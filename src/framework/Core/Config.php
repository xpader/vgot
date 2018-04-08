<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 17-4-23
 * Time: 下午4:55
 */
namespace vgot\Core;

use vgot\Exceptions\ApplicationException;

class Config
{

	protected $configs = [
		'' => []
	];
	protected $loadedConfigs = [''];

	/**
	 * Search config file from this path list
	 *
	 * @var array
	 */
	private $searchPath = [];

	public function __construct($configPath, $commonConfigPath=null)
	{
		$this->searchPath[] = $configPath;

		if ($commonConfigPath !== null) {
			$this->searchPath[] = $commonConfigPath;
		}

		$this->load('application');
	}

	/**
	 * Load Config
	 *
	 * This will re-read the config file
	 *
	 * @param string $name
	 * @param bool $useSection Is config in a stand alone space, if true, you must call get use $config
	 * @param bool $return Return config array
	 * @param bool $forceReload Is force reload file
	 * @return mixed
	 * @throws
	 */
	public function load($name, $useSection=false, $return=false, $forceReload=false) {
		$isLoaded = $useSection ? isset($this->configs[$name]) : in_array($name, $this->loadedConfigs);

		if (!$isLoaded || $forceReload) {
			$config = $this->loadFile($name);

			if ($useSection) {
				$this->configs[$name] = $config;
			} else {
				$this->configs[''] += $config;
				!$isLoaded && $this->loadedConfigs[] = $name;
			}
		}

		if ($return) {
			return isset($config) ? $config : ($useSection ? $this->configs[$name] : $this->configs['']);
		}
	}

	/**
	 * Get an exists config
	 *
	 * @param string $config Config name
	 * @return array|null
	 */
	public function item($config)
	{
		return isset($this->configs[$config]) ? $this->configs[$config] : null;
	}

	/**
	 * Get config value
	 *
	 * @param string $key
	 * @param string $section If config load to separate space, must set this value to separate name
	 * @return mixed
	 */
	public function get($key, $section='')
	{
		if ($section && !isset($this->configs[$section])) {
			$this->load($section, true);
		}

		return isset($this->configs[$section][$key]) ?
			$this->configs[$section][$key] : null;
	}

	/**
	 * Set a config value
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param string $section If set to separate space, this value must set to separate name
	 */
	public function set($key, $value, $section='')
	{
		if ($section != '' && !isset($this->configs[$section])) {
			$this->load($section, true);
		}

		$this->configs[$section][$key] = $value;
	}

	/**
	 * Load config file
	 *
	 * @param string $name
	 * @return mixed
	 * @throws ApplicationException
	 */
	protected function loadFile($name)
	{
		foreach ($this->searchPath as $dir) {
			$file = $dir.'/'.$name.'.php';
			if (is_file($file)) {
				return (include $file);
			}
		}

		throw new ApplicationException("Can not found config: $name.\n");
	}

}