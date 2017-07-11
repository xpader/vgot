<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/4/30
 * Time: 11:12
 */

namespace vgot\Core;


use vgot\Exceptions\ApplicationException;

class View
{

	protected $viewsPath;
	protected $commonViewsPath;
	protected $viewVars = [];

	protected static $instance;

	public $title = '';

	public function __construct($viewsPath, $commonViewsPath=null)
	{
		$this->viewsPath = $viewsPath;
		$this->commonViewsPath = $commonViewsPath;

		self::$instance = $this;
	}

	public function render($name, $vars=null, $return=false) {
		$viewFile = $this->viewsPath.DIRECTORY_SEPARATOR.$name.'.php';

		if (!is_file($viewFile)) {
			$viewFile = $this->commonViewsPath.DIRECTORY_SEPARATOR.$name.'.php';
			if (!is_file($viewFile)) {
				throw new ApplicationException("No Found View File '$name'");
			}
		}

		return $this->parseView($viewFile, $vars, $return);
	}

	/**
	 * Load View Vars
	 *
	 * @param array|object|string $vars
	 * @param mixed $value
	 * @return void
	 */
	public function vars($vars, $value=null)
	{
		if ((is_array($vars) || is_object($vars))) {
			$this->viewVars = array_merge($this->viewVars, (array)$vars);
		} else {
			$this->viewVars[$vars] = $value;
		}
	}

	/**
	 * Parse view
	 *
	 * @param string $__pViewFile View file path
	 * @param array|object $__pViewVars View vars to assign
	 * @param bool $__pReturnBuffer Return view buffer and not output
	 * @return string
	 */
	protected function parseView($__pViewFile, $__pViewVars=null, $__pReturnBuffer=false)
	{
		$this->vars($__pViewVars);

		//extract view vars
		extract($this->viewVars, EXTR_OVERWRITE);

		$__pReturnBuffer && ob_start();

		include $__pViewFile;
		//nvLog("Include View File '$__nvViewName'");

		if ($__pReturnBuffer) {
			$buffer = ob_get_contents();
			ob_end_clean();
			//nvLog("Return Buffer From View '$__nvViewName'");
			return $buffer;
		}
	}

}