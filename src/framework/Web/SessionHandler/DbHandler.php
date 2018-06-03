<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2018/4/22
 * Time: 00:16
 */

namespace vgot\Web\SessionHandler;

use vgot\Core\Application;
use vgot\Database\QueryBuilder;
use vgot\Exceptions\ApplicationException;

/*
Create table using (for MySQL):

CREATE TABLE `sessions` (
  `sid` varchar(128) NOT NULL,
  `data` blob NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`)
);
*/

/**
 * vgot Session Database Handler
 * @package vgot\Web\SessionHandler
 */
class DbHandler implements \SessionHandlerInterface
{

	/**
	 * @var QueryBuilder
	 */
	protected $db;
	public $table;

	public function close()
	{
		return true;
	}

	public function destroy($sid)
	{
		$this->db->where(['sid'=>$sid])->delete($this->table);
		return true;
	}

	public function gc($maxLifeTime)
	{
		$this->db->where(['timestamp <'=>time()-$maxLifeTime])->delete($this->table);
		return true;
	}

	/**
	 * @param string $savePath db provider/table
	 * @param string $name
	 * @return bool
	 * @throws ApplicationException
	 */
	public function open($savePath, $name)
	{
		list($provider, $table) = explode('/', $savePath);
		
		$this->db = Application::getInstance()->$provider;

		if (!($this->db instanceof QueryBuilder)) {
			throw new ApplicationException('Cache session handler must implements of '.QueryBuilder::class.'.');
		}
		
		$this->table = $table;

		return true;
	}

	public function read($sid)
	{
		$data = $this->db->select('data')->from($this->table)->where(['sid'=>$sid])->fetchColumn();
		return $data ?: '';
	}

	public function write($sid, $sessionData)
	{
		return (bool)$this->db->insert($this->table, ['sid'=>$sid, 'data'=>$sessionData,
			'timestamp'=>time(), 'ip_address'=>getApp()->input->clientIp()], true);
	}

}