<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2018/7/1
 * Time: 22:44
 */

namespace vgot\Utilities;

class ArrayUtil
{

	/**
	 * Export an array
	 *
	 * @param array $var
	 * @param bool $return
	 * @return string|void
	 */
	public static function export($var, $return=false)
	{
		$code = self::varExport($var);

		if ($return) {
			return $code;
		}

		echo $code;
	}

	protected static function varExport($var, $level=0)
	{
		switch (gettype($var)) {
			case 'array':
				$tabEnd = str_repeat("\t", $level);
				$tab = $tabEnd."\t";
				$code = "[\r\n";

				foreach ($var as $key => $val) {
					is_string($key) && $key = "'$key'";
					if (is_array($val)) {
						$code .= $tab.$key.' => '.self::varExport($val, $level + 1);
					} else {
						$code .= $tab.$key.' => '.self::varExport($val);
					}
					$code .= ",\r\n";
				}

				$var && $code = substr_replace($code, '', -3, 1);
				$code .= "$tabEnd]";
				break;
			case 'string':
				$code = '\''.addcslashes($var,'\\\'').'\'';
				break;
			case 'boolean':
				$code = $var ? 'true' : 'false';
				break;
			case 'NULL':
				$code = 'null';
				break;
			default:
				$code = $var;
		}

		return $code;
	}

}