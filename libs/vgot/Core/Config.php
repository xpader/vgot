<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 17-4-23
 * Time: 下午4:55
 */

namespace vgot\Core;


class Config
{

	protected $configs;
	protected $loadedConfigs = ['config'];

	/**
	 * 配置将从此路径数组中搜索
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
	 * @return void|array
	 * @throws
	 */
	public function load($name, $useSection=false, $return=false, $forceReload=false) {
		$isLoaded = in_array($name, $this->loadedConfigs);

		if (!$forceReload && $isLoaded) {
			if ($return) {
				return isset($this->configs[$name]) ? $this->configs[$name] : null;
			} else {
				return;
			}
		}

		$config = self::sload($name, $this->appIndex);

		if ($config === null) {
			throw new \Exception("Can not found config: $name.\n");
		}

		if ($useSection) {
			$this->configs[$name] = $config;
		} else {
			$this->configs['config'] += $config;
		}

		if (!$isLoaded) {
			$this->loadedConfigs[] = $name;
		}

		if ($return) return $config;
	}

}