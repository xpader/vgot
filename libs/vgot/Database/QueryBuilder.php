<?php
/**
 * Created by PhpStorm.
 * User: Pader
 * Date: 2017/5/3
 * Time: 22:28
 */

namespace vgot\Database;


class QueryBuilder extends Connection {

	protected $builder = [];
	protected $table;

	public function select($fields, $quote=true)
	{
		$this->builder['select'] = $fields;
		$this->builder['select_quote'] = $quote;
		return $this;
	}

	public function from($table, $alias=null)
	{
		$this->table = $this->quoteKeys($table);
		return $alias ? $this->alias($alias) : $this;
	}

	public function alias($alias)
	{
		$this->builder['alias'] = $alias;
		return $this;
	}

	public function join()
	{
		return $this;
	}

	public function where()
	{
		return $this;
	}

	public function having()
	{
		return $this;
	}

	/**
	 * @param string|array $groupBy
	 * @return $this
	 */
	public function groupBy($groupBy)
	{
		$this->builder['group_by'] = $groupBy;
		return $this;
	}

	/**
	 * @param array|string $order
	 * @return $this
	 */
	public function orderBy($order)
	{
		$this->builder['order_by'] = $order;
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
		//if (!$this->table) {
		//	throw new DatabaseException('Query build error, No table were selected!', $this->di);
		//}

		//SELECT
		if (isset($this->builder['select'])) {
			$sql = 'SELECT '.
				($this->builder['select_quote']
					? $this->quoteKeys($this->builder['select'])
					: $this->builder['select']);
		} else {
			$sql = 'SELECT *';
		}

		//FROM
		if ($this->table) {
			$sql .= " FROM {$this->table}";

			//AS
			if (isset($this->builder['alias'])) {
				$sql .= " `{$this->builder['alias']}`";
			}
		}

		//GROUP BY
		if (isset($this->builder['group_by'])) {
			$sql .= ' GROUP BY '.$this->quoteKeys($this->builder['group_by']);
		}

		//ORDER BY
		if (isset($this->builder['order_by'])) {
			$orderBy = $this->builder['order_by'];

			//convert order by string to array
			if (!is_array($orderBy)) {
				$orderBy = preg_replace('/\s+/', ' ', $orderBy);
				$arr = array_map('trim', explode(',', $orderBy));
				$orderBy = [];

				foreach ($arr as $ostr) {
					list($field, $sort) = explode(' ', $ostr);
					$orderBy[$field] = $sort;
				}
			}

			$order = '';

			foreach ($orderBy as $field => $sort) {
				$order != '' && $order .= ', ';

				if ($sort === SORT_ASC) {
					$sort = 'ASC';
				} elseif ($sort === SORT_DESC) {
					$sort = 'DESC';
				} else {
					$sort = strtoupper($sort);
				}

				$order .= $this->quoteKeys($field).' '.$sort;
			}

			$sql .= ' ORDER BY '.$order;
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
		$this->table = null;

		return $sql;
	}

	//Fetch one row from query result
	public function fetch($fetchType=DB::FETCH_ASSOC)
	{
		$this->prepareQuery();
		return parent::fetch($fetchType);
	}

	//Fetch all rows from query result
	public function fetchAll($fetchType=DB::FETCH_ASSOC)
	{
		$this->prepareQuery();
		return parent::fetchAll($fetchType);
	}

	//Fetch a column value in first result row
	public function fetchColumn($col=0)
	{
		$this->prepareQuery();
		return parent::fetchColumn($col);
	}

	//public function distinct($field)
	//{
	//	$this->builder['select'] = "distinct($field)";
	//	return $this;
	//}

	/**
	 * Count rows
	 *
	 * @param string $field
	 * @return int|null
	 */
	public function count($field='*')
	{
		$this->builder['select'] = "count($field)";
		return $this->fetchColumn();
	}

	/**
	 * Build and do query for fetch result
	 */
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
	 * @param bool $single Is a single field in top level
	 * @return string
	 */
	protected function quoteKeys($keys, $single=false)
	{
		if (is_array($keys)) {
			$qk = array();
			foreach($keys as $key) {
				$qk[] = $this->quoteKeys($key);
			}
			return join(', ', $qk);

		} elseif (!$single && strpos($keys,',') !== false) {
			//split with , except in () and ''
			$xkeys = preg_split('/,(?![^(\']*[\)\'])/', $keys);

			if (!isset($xkeys[1]) || $xkeys[0] == $keys) {
				return $this->quoteKeys($keys, true);
			} else {
				return $this->quoteKeys($xkeys);
			}

		} else {
			$keys = $col = trim($keys);
			$str = $pre = $func = $as = '';
			$quote = true;

			//as alias
			if (stripos($keys,' AS ') !== false) {
				list($col, $as) = explode(' AS ', str_replace(' as ', ' AS ', $keys));
				$as = trim($as, ' `');
			}

			//used function
			if (preg_match('/^(\w+)\s*\(\s*(.+)?\s*\)$/i', $col, $m)) {
				$func = strtoupper($m[1]);

				if (!isset($m[2])) {
					$col = '';
					$quote = false;
				} elseif (strpbrk($m[2], ',()') !== false) { //nested function
					$col = $this->quoteKeys($m[2]);
					$quote = false;
				} else {
					$col = $m[2];
				}
			}

			//has prefix
			if (strpos($col, '.') !== false) {
				list($pre, $col) = explode('.', $col);
				$pre = trim($pre, ' `');
			}

			//make return string
			$pre && $str = "`$pre`.";
			($col != '*' && $quote && !ctype_digit($col)) && $col = "`$col`";
			$func ? $str = "$func($str$col)" : $str .= $col;
			$as && $str .= " AS `$as`";

			return $str;
		}
	}

}