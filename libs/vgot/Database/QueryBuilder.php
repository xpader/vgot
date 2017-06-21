<?php
/**
 * Created by PhpStorm.
 * User: Pader
 * Date: 2017/5/3
 * Time: 22:28
 */

namespace vgot\Database;


use vgot\Exceptions\DatabaseException;

class QueryBuilder extends Connection {

	protected $builder = [];
	protected $table;

	public function select($fields)
	{
		$this->builder['select'] = $fields;
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
		$this->table = $table;
		return $alias ? $this->alias($alias) : $this;
	}

	public function alias($alias)
	{
		$this->builder['alias'] = $alias;
		return $this;
	}

	/**
	 * Limit
	 *
	 * @param int $num Limit or offset when $limit is null
	 * @param int $limit
	 * @return self
	 */
	public function limit($num, $limit=null)
	{
		if ($limit !== null) {
			$this->builder['limit'] = $limit;
			$this->builder['offset'] = $num;
		} else {
			$this->builder['limit'] = $num;
		}

		return $this;
	}

	public function offset($num)
	{
		$this->builder['offset'] = $num;
		return $this;
	}

	public function insert()
	{}

	public function delete()
	{}

	public function update()
	{}

	/**
	 * Build sql from query builder
	 *
	 * @return string
	 * @throws
	 */
	public function buildSql()
	{
		if (!$this->table) {
			throw new DatabaseException('Query build error, No table were selected!', $this->di);
		}

		//SELECT
		if (isset($this->builder['select'])) {
			$sql = 'SELECT '.$this->quoteFields($this->builder['select']);
		} else {
			$sql = 'SELECT *';
		}

		//FROM
		$sql .= " FROM `{$this->table}`";

		//AS
		if (isset($this->builder['alias'])) {
			$sql .= " `{$this->builder['alias']}`";
		}

		//LIMIT, OFFSET
		if (isset($this->builder['limit'])) {
			$sql .= ' LIMIT ';

			if (isset($this->builder['offset'])) {
				$sql .= "{$this->builder['offset']},";
				unset($this->builder['offset']);
			}

			$sql .= $this->builder['limit'];
		}

		if (isset($this->builder['offset'])) {
			$sql .= " OFFSET {$this->builder['offset']}";
		}

		$this->builder = [];

		return $sql;
	}

	/**
	 * Fetch one row from query result
	 *
	 * @param int $fetchType
	 * @return array|null
	 */
	public function fetch($fetchType=DB::FETCH_ASSOC)
	{
		$this->prepareQuery();
		return parent::fetch($fetchType);
	}

	/**
	 * Fetch all rows from query result
	 *
	 * @param int $fetchType
	 * @return array
	 */
	public function fetchAll($fetchType=DB::FETCH_ASSOC)
	{
		$this->prepareQuery();
		return parent::fetchAll($fetchType);
	}

	protected function prepareQuery()
	{
		if ($this->builder || $this->table) {
			$this->query($this->buildSql());
		}
	}

	/**
	 * Convert Keys To SQL Format
	 *
	 * @param string|array $keys
	 * @return string
	 */
	protected function quoteFields($keys)
	{
		if (is_array($keys)) {
			$qk = array();
			foreach($keys as $key) {
				$qk[] = $this->quoteFields($key);
			}
			return join(',',$qk);
		} elseif (strpos($keys,',') !== false) {
			$keys = explode(',',$keys);
			return $this->quoteFields($keys);
		} else {
			$keys = $col = trim($keys);
			$str = $pre = $func = $as = '';

			//as alias
			if (stripos($keys,' AS ') !== false) {
				list($col, $as) = explode(' AS ', str_replace(' as ', ' AS ', $keys));
				$as = trim($as, ' `');
			}

			//used function
			if (preg_match('/^(\w+)\s*\(\s*(.+)\s*\)/i', $col, $m)) {
				$func = $m[1];
				$col = $m[2];
			}

			//has prefix
			if (strpos($col,'.') !== false) {
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