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

	public function select()
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

}