<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/4/23
 * Time: 02:26
 */

return [
	'id' => 'Basic',
	'base_url' => 'http://127.0.0.1/dev/vgot/public/',
	'entry_file' => 'index.php',

	//The providers to register
	'providers' => [
		'security' => [
			'class' => 'vgot\Core\Security',
			'arguments' => ['test'],
			'propertys' => ['']
		],
		'cache' => [
			'class' => 'vgot\Cache\FileCache',
			'arguments' => [
				[
					'stor_dir' => BASE_PATH.'/resource/cache',
					'cache_in_memory' => true,
					'dir_level' => 2
				]
			]
		],
		'session' => [
			'class' => 'vgot\Web\Session',
			'arguments' => [
				[
					'lifetime' => 86400,
					'handler' => 'cache'
				]
			]
		]
	],

	//Output
	'output_charset' => 'utf-8',
	'output_gzip' => true,
	'output_gzip_level' => 8,
	'output_gzip_minlen' => 1024, //1KB
	'output_gzip_force_soft' => false, //是否强制使用框架自带的gzip压缩，否则会检测是否可以启用PHP内置压缩

	/**
	 * 设置系统的错误捕捉事件，如果不设此选项，则使用比方内置方法
	 *
	 * @var callable
	 */
	'set_error_handler' => null,

	/**
	 * Set system bootstrap event
	 *
	 * on_boot run before router parse and controller instance.
	 * It helpful for something no need controller, for example in error page.
	 */
	'on_boot' => null
];