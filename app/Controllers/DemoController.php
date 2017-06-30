<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 17-4-24
 * Time: 上午1:43
 */
namespace app\Controllers;

use vgot\Database\DB;

class DemoController extends \vgot\Core\Controller
{

	public function index()
	{
		echo 'WhatsApp';
	}

	public function dev()
	{
		$this->aaa();
	}

	public function gzip()
	{
		echo '<div>'.str_repeat('Hello World ', 600).'</div>';
		echo '<div>'.str_repeat('Hello World ', 500).'</div>';
	}

	public function view()
	{
		$this->render('index/index');
	}

	public function db()
	{
		$db = DB::connection('default', true);

		//$result = $db->from('text')->orderBy(['id'=>SORT_ASC])->groupBy('id')->fetchAll();
		//print_r($result);

		//$db->select('t.id as uniqid,text')->from('text')->alias('t');
		//$result = $db->offset(1)->limit(1)->fetchAll();
		//print_r($result);

		$result = $db->from('text')->alias('t')->orderBy(['id'=>SORT_DESC])->limit(2,1)->fetchAll();
		print_r($result);

		$result= $db->select('  min ( id ) as min_id , sum(t.id ) as sum, max( id) max_id')->from('text')->alias('t')->fetch();
		print_r($result);

		$result= $db->select('     uuid (     )     uuid  ')->fetchColumn();
		print_r($result."\n");

		$result= $db->select('*')->from('  zentao  .  zt_grouppriv   as    a   ')->groupBy('company,group')->fetchAll();
		print_r($result);

		$db->insert('text', ['text'=>'Hello World']);

		print_r($db->getQueryRecords());
	}

	public function dbWhere()
	{
		$db = DB::connection('default', true);

		$result= $db->from('text')->where(['OR', ['id'=>3, 'text'=>4], ['id'=>1, 'text'=>'Hello'], 'id like'=>'12'])->fetch();
		//$result= $db->from('text')->where(['id'=>3])->fetch();
		print_r($result);


		print_r($db->getQueryRecords());
	}

	public function sqlite()
	{
		$db = DB::connection('sqlite');
	}

}