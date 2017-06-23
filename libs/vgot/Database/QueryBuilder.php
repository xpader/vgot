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

	public function select($fields, $quote=true)
	{
		$this->builder['select'] = $fields;
		$this->builder['select_quote'] = $quote;
		return $this;
	}

	public function from($table, $alias=null)
	{
		$this->table = $this->quoteTable($table);
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

	public function insert($table, $data, $params=null, $replace=false)
	{
		$table = $this->quoteTable($table);
		$keys = $this->quoteKeys(array_keys($data));
		$values = $this->quoteValues($data);

		$sql = ($replace ? 'REPLACE' : 'INSERT')." INTO $table($keys) VALUES($values)";

		//use exec instead query for better
		return $this->query($sql);
	}

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
		}

		if (!$single && strpos($keys,',') !== false) {
			//split with comma[,] except in brackets[()] and single quots['']
			$xkeys = preg_split('/,(?![^(\']*[\)\'])/', $keys);

			if (!isset($xkeys[1]) || $xkeys[0] == $keys) {
				return $this->quoteKeys($keys, true);
			} else {
				return $this->quoteKeys($xkeys);
			}
		}

		$keys = $col = $this->trim($keys);
		$str = $pre = $func = $alias = $as = '';
		$quote = true;

		//as
		if (($asa = $this->splitAs($keys)) !== false) {
			list($col, $as, $alias) = $asa;
		}

		//used function, two [?] for 1 is not match after space, 2 is not only space in brackets
		if (preg_match('/^(\w+)\s*\(\s*(.+?)?\s*\)$/i', $col, $m)) {
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

		//add prefix
		$pre && $str = "`$pre`.";

		//add quote
		($col != '*' && $quote && !ctype_digit($col)) && $col = "`$col`";

		//add function
		$func ? $str = "$func($str$col)" : $str .= $col;

		//add alias
		$as && $str .= "$as`$alias`";

		return $str;
	}

	protected function quoteTable($table)
	{
		if (is_array($table)) {
			$str = '';
			foreach ($table as $t) {
				$str != '' && $str .= ',';
				$str .= $this->quoteTable($t);
			}
			return $str;
		}

		if (strpos($table, ',') !== false) {
			return $this->quoteTable(explode(',', $table));
		}

		$table = $this->trim($table);
		$db = $as = $alias = '';

		//as alias
		if (($asa = $this->splitAs($table)) !== false) {
			list($table, $as, $alias) = $asa;
		}

		//remove space char, because may have " db .  table" situation
		$table = str_replace(' ', '', $table);

		if (strpos($table, '.') !== false) {
			list($db, $table) = explode('.', $table);
		}

		if (!$this->hasPrefix($table)) {
			$table = $this->tableName($table);
		}

		$str = '';

		$db && $str = "`$db`.";
		$str .= "`$table`";
		$as && $str .= "$as`$alias`";

		echo $str;

		return $str;
	}

	/**
	 * @param string|array $values
	 * @return string Keys
	 */
	public function quoteValues($values)
	{
		if (is_array($values)) {
			$vals = array();
			foreach($values as $val) {
				$vals[] = $this->quoteValues($val);
			}
			return join(',', $vals);
		} else {
			return $this->quote($values);
		}
	}

	/**
	 * Split the table/field alias string
	 *
	 * @param string $str
	 * @return array|bool
	 */
	protected function splitAs($str)
	{
		//as alias
		if (stripos($str,' AS ') !== false) {
			list($col, $alias) = explode(' AS ', str_replace(' as ', ' AS ', $str));
			$alias = trim($alias, ' ');
			$as = ' AS ';
		} elseif (preg_match('/[^\s]\s[\w\-]+$/i', $str)) {
			//sure it's alias name after space
			//like "field alias", not "some )" or any other string after space not a alias
			list($col, $alias) = preg_split('/\s(?=[\w\-]+$)/i', $str);
			$as = ' ';
		} else {
			return false;
		}

		return [$col, $as, $alias];
	}

	protected function trim($str)
	{
		return preg_replace('/\s+/', ' ', trim($str, ' `'));
	}

}