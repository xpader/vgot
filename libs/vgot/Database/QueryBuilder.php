<?php
/**
 * Created by PhpStorm.
 * User: Pader
 * Date: 2017/5/3
 * Time: 22:28
 */

namespace vgot\Database;


class QueryBuilder extends Connection {

	protected $_select;
	protected $_orderBy;
	protected $_join;
	protected $_from;
	protected $_alias;

	public function select($fields)
	{
		return $this;
	}

	public function orderBy()
	{
		return $this;
	}

	public function where()
	{
		return $this;
	}

	public function join()
	{
		return $this;
	}

	public function from($table, $alias=null)
	{
		$this->_from = $table;

		if ($alias) {
			$this->alias($alias);
		}

		return $this;
	}

	public function alias($alias)
	{
		$this->_alias = $alias;
		return $this;
	}

	public function insert()
	{}

	public function delete()
	{}

	public function update()
	{}

	/**
	 * Convert Keys To SQL Format
	 *
	 * @param string|array $keys
	 * @return string
	 */
	protected function quoteFields($keys)
	{
		if (is_array($keys)) {
			$Qkeys = array();
			foreach($keys as $key) {
				$Qkeys[] = $this->quoteFields($key);
			}
			return join(',',$Qkeys);
		} elseif (strpos($keys,',') !== false) {
			$keys = explode(',',$keys);
			return $this->quoteFields($keys);
		} else {
			$keys = $col = trim($keys);
			$str = $pre = $func = $as = '';

			//as alias
			if (stripos($keys,' AS ') !== FALSE) {
				list($col, $as) = explode(' AS ', str_replace(' as ', ' AS ', $keys));
				$as = trim($as, ' `');
			}

			//used function
			if (preg_match('/^(\w+)\s*\(\s*(.+)\s*\)/i', $col, $m)) {
				$func = $m[1];
				$col = $m[2];
			}

			//has prefix
			if (strpos($col,'.') !== FALSE) {
				list($pre, $col) = explode('.', $col);
				$pre = trim($pre, ' `');
			}

			//make return string
			$pre && $str = "`$pre`.";
			($col != '*' && !ctype_digit($col)) && $col = "`$col`";
			$func ? $str = "$func($str$col)" : $str .= $col;
			$as && $str .= " AS `$as`";

			return $str;
		}
	}

}