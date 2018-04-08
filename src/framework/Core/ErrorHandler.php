<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/6/15
 * Time: 18:02
 */
namespace vgot\Core;

use vgot\Exceptions\DatabaseException;
use vgot\Exceptions\HttpNotFoundException;

class ErrorHandler
{

	public static function errorHandler($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno)) {
			// This error code is not included in error_reporting, so let it fall
			// through to the standard PHP error handler
			return false;
		}

		switch ($errno) {
			case E_USER_ERROR:
				echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
				echo "  Fatal error on line $errline in file $errfile";
				echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
				echo "Aborting...<br />\n";
				exit(1);
				break;

			case E_USER_WARNING:
				echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
				break;

			case E_USER_NOTICE:
				echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
				break;

			default:
				echo "Unknown error type: [$errno] $errstr<br />\n";
				break;
		}

		/* Don't execute PHP internal error handler */
		return true;
	}

	/**
	 * Exception Handler
	 *
	 * @param \Exception $exception
	 * @throws \Exception
	 */
	public static function exceptionHandler($exception)
	{
		$app = Application::getInstance();

		if ($exception instanceof HttpNotFoundException) {
			header('HTTP/1.1 404 Not Found');
			header('Status: 404 Not Found');
			$app->view->render('errors/404');
		} elseif ($exception instanceof DatabaseException) {
			header('HTTP/1.1 500 Internal Server Error');
			header('Status: 500 Internal Server Error');
			$app->view->render('errors/db', compact('exception'));
		} else {
			header('HTTP/1.1 500 Internal Server Error');
			header('Status: 500 Internal Server Error');
			$app->view->render('errors/500', compact('exception'));
		}
	}

	public static function shutdownHandler()
	{
		var_dump(func_get_args());
	}

}