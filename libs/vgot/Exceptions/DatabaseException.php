<?php
/**
 * Created by PhpStorm.
 * User: Pader
 * Date: 2017/5/3
 * Time: 22:41
 */

namespace vgot\Exceptions;

use vgot\Database\DriverInterface;

class DatabaseException extends \Exception {

	protected $code;
	protected $sql;
	protected $error;

	/**
	 * Constructor.
	 *
	 * @param string $message
	 * @param DriverInterface|string $di
	 * @param string $sql
	 */
	public function __construct($message, DriverInterface $di=null, $sql='')
	{
		if ($di) {
			if ($di instanceof DriverInterface) {
				$code = $di->getErrorCode();
				if ($error = $di->getErrorMessage()) {
					$this->error = $error;
				}
			} else {
				$this->error = $di;
			}
		}

		parent::__construct($message);

		$this->code = isset($code) ? $code : 0;

		$sql && $this->sql = $sql;
	}

	public function getSql()
	{
		return $this->sql;
	}

	public function getError()
	{
		return $this->error;
	}

	public function __toString()
	{
		$str = __CLASS__ . ": {$this->message}\n";

		if ($this->code) {
			$str .= "Code: {$this->code}\n";
		}

		if ($this->error) {
			$str .= "Messsage: {$this->error}\n";
		}

		if ($this->sql) {
			$str .= "Query: {$this->sql}\n";
		}

		$str .= "Stack trace:\n".$this->getTraceAsString();

		return $str;
	}

}