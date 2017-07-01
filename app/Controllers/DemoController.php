<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 17-4-24
 * Time: 上午1:43
 */
namespace app\Controllers;

use vgot\Core\Application;
use vgot\Database\DB;

class DemoController extends \vgot\Core\Controller
{

	public function index()
	{
		echo 'WhatsApp';
	}

	public function dev()
	{
		$app = \vgot\app();
		$a = $app->db->from('text')->limit(10)->fetchAll();

		print_r($app->db->getQueryRecords());

		print_r($a);
	}

	public function gzip()
	{
		echo \vgot\app()->output->getMode();

		var_dump(ini_get('zlib.output_compression'));

		echo '<div>'.str_repeat('Hello World ', 600).'</div>';

		//ob_start();
		echo '<div>'.str_repeat('Hello World ', 500).'</div>';
		//$contents = ob_get_contents();
		//ob_end_clean();
	}

	public function view()
	{
		$this->render('index/index');
	}

	public function db()
	{
		$db = Application::getInstance()->db;

		//$result = $db->from('text')->orderBy(['id'=>SORT_ASC])->groupBy('id')->fetchAll();
		//print_r($result);

		//$db->select('t.id as uniqid,text')->from('text')->alias('t');
		//$result = $db->offset(1)->limit(1)->fetchAll();
		//print_r($result);

		$result = $db->from('text')->alias('t')->orderBy(['id'=>SORT_DESC])->limit(2,1)->fetchAll();
		print_r($result);

		$result = $db->select('  min ( id ) as min_id , sum(t.id ) as sum, max( id) max_id')->from('text')->alias('t')->fetch();
		print_r($result);

		$result = $db->select('     uuid (     )     uuid  ')->fetchColumn();
		print_r($result."\n");

		$result = $db->select('*')->from('  zentao  .  zt_grouppriv   as    a   ')->groupBy('company,group')->fetchAll();
		print_r($result);

		$db->where(['id'=>60])->update('text', ['ss'=>uniqid()]);

		$result = $db->leftJoin('t2 w', 'id')->from('text t')->where(['t.id'=>60])->fetch();
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

}