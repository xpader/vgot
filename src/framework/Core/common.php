<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/7/18
 * Time: 17:14
 */

function configClass($object, array $config) {
	foreach ($config as $k => $v) {
		$k = preg_replace_callback('/_([a-z\d])/', function($m) {
			return strtoupper($m[1]);
		}, $k);

		if (property_exists($object, $k)) {
			$object->$k = $v;
		}
	}
}

function getApp() {
	return \vgot\Core\Application::getInstance();
}