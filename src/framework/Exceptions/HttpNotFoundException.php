<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 17-4-24
 * Time: 上午1:40
 */

namespace vgot\Exceptions;

class HttpNotFoundException extends \Exception
{

	protected $message = '404 Page Not Found.';

}