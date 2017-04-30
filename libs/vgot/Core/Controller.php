<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 17-4-24
 * Time: 上午1:21
 */

namespace vgot\Core;


abstract class Controller extends Base
{

	protected function render($name, $vars=null, $return=false)
	{
		return $this->view->render($name, $vars, $return);
	}

}