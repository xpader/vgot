<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/7/15
 * Time: 01:17
 */

namespace vgot\Cache;


interface CacheInterface
{

	public function get($key, $defaultValue=null);

	public function set($key, $data, $expire=0);

	public function delete($key);

}