<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 17-4-24
 * Time: 上午1:43
 */
namespace app\Controllers;

use vgot\Database\DB;
use vgot\Web\Url;

class DemoController extends \vgot\Core\Controller
{

	public function index()
	{
		echo 'WhatsApp';
	}

	public function __rtest()
	{
		echo '123';
	}

	public function app()
	{
		$app = getApp();
	}

	public function gzip()
	{
		echo getApp()->output->getMode();

		var_dump(ini_get('zlib.output_compression'));

		echo '<div>'.str_repeat('Hello World ', 600).'</div>';

		//ob_start();
		echo '<div>'.str_repeat('Hello World ', 500).'</div>';
		//$contents = ob_get_contents();
		//ob_end_clean();
	}

	public function view()
	{
		//$this->render('index/index');
	}

	public function db()
	{
		$db = getApp()->db;

		//$result = $db->from('text')->orderBy(['id'=>SORT_ASC])->groupBy('id')->fetchAll();
		//print_r($result);

		//$db->select('t.id as uniqid,text')->from('text')->alias('t');
		//$result = $db->offset(1)->limit(1)->fetchAll();
		//print_r($result);

		$result = $db->from('text')->alias('t')->orderBy(['id'=>SORT_DESC])->limit(1, 2)->scalar();
		print_r($result);

		$result = $db->select('  min ( id ) as min_id , sum(t.id ) as sum, max( id) max_id')->from('text')->alias('t')->fetch();
		print_r($result);

		$result = $db->select('     uuid (     )     uuid  ')->scalar();
		print_r($result."\n");

		$result = $db->select('*')->from('  zentao  .  zt_grouppriv   as    a   ')->groupBy('module,group')->fetchAll();
		print_r($result);

		$db->where(['id'=>60])->update('text', ['id'=>3]);

		$result = $db->leftJoin('t2 w', ['and', 't.id=w.id', ['or', 't.id'=>60, 't.text'=>'H']])->from('text t')->where(['t.id'=>60])->fetch();
		print_r($result);

		$result = $db->from('text')->where(['id between'=>[50, 55]])->union()->from('text')->where(['id'=>58])->fetchAll();
		print_r($result);

		//$db->insert('text', ['text'=>'Hello World']);

		print_r($db->getQueryRecords());
	}

	public function dbWhere()
	{
		$db = DB::connection();

		//$result= $db->where(['id'=>23])->update('text', ['text'=>file_get_contents('http://php.net/manual/zh/pdo.lastinsertid.php')]);
		$result = $db->where(['id'=>48])->delete('text');

		print_r($result);


		print_r($db->getQueryRecords());
	}

	public function sqlite()
	{
		$db = DB::connection('sqlite', 1);
		//$db->exec('create table test(id INTEGER PRIMARY KEY NOT NULL, `text` text NOT NULL, date int NOT NULL)');

		$result = $db->from('test')->where(['id >'=>5, 'id !'=>'aaa\'asd'])->fetchAll();
		print_r($result);

		//$db->insert('test', ['text'=>'Hello World', 'date'=>time()]);
		//echo $db->insertId();

		print_r($db->getQueryRecords());
	}

	public function cache()
	{
		$app = getApp();

		$app->register('cache', 'vgot\Cache\FileCache', [
			[
				'stor_dir' => BASE_PATH.'/resource/cache',
				'cache_in_memory' => true,
				'dir_level' => 2
			]
		]);

		//$app->register('cache', 'vgot\Cache\DbCache');

		//$app->register('cache', 'vgot\Cache\Memcache', [
		//	[
		//		'host' => '127.0.0.1',
		//		'port' => '11211',
		//		'key_prefix' => 'vgottest_'
		//	]
		//]);

		//$app->register('cache', 'vgot\Cache\Redis', [
		//	[
		//		'host' => '127.0.0.1',
		//		'key_prefix' => 'vgottest_',
		//		'serialize' => 'igbinary',
		//		'max_key_length' => 64
		//	]
		//]);

		///** @var \vgot\Cache\Memcache $cache */
		$cache = $app->cache;

		//$cache->createTable();

		$data = $cache->get('test123');

		if ($data === null) {
			$data = [date('Y-m-d H:i:s'), 1, 2, 3];
			var_dump($cache->set('test123', $data, 10));
		}

		var_dump($data);

		//print_r($app->db->getQueryRecords());

		$val = $cache->set('This is a very long long key name_long_long_long_long_name_long_long_long_long', '123123', 10);
		var_dump($val);

		$cache->gc();
	}

	public function input()
	{
		$app = getApp();

		var_dump($app->input->server('HTTP_USER_AGENT', 'default'));
		var_dump($app->input->clientIp());
	}

	public function url()
	{
		echo Url::site(['abc/deep/def', 'hello'=>'world', 'p'=>'arams']);
	}

	public function testPdoReturnFalse() {
		$value = $this->db->from('urls')->where(['id'=>213])->value();
		var_dump($value);
	}

}