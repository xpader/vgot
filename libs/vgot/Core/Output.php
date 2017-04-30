<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/4/30
 * Time: 11:18
 */

namespace vgot\Core;


class Output
{

	protected $gzipType = null;

	public function __construct()
	{
		$config = Application::getInstance()->config;

		if ($config->get('output_gzip') && ($config->get('output_gzip_force_soft') || !$this->enableHardGzip())) {
			ob_start('\\'.self::class.'::gzipEncode');
			$this->gzipType = 'soft';
		} else {
			ob_start();
		}
	}

	public function __destruct()
	{
		ob_end_flush();
	}

	public function flush()
	{
		ob_flush();
		flush();
	}

	public function getGzipType()
	{
		return $this->gzipType;
	}

	protected function enableHardGzip()
	{
		if (!headers_sent() && ini_set('zlib.output_compression', 'On') !== false) {
			ini_set('zlib.output_compression_level', Application::getInstance()->config->get('output_gzip_level'));
			$this->gzipType = 'hard';
			return true;
		}

		return false;
	}

	public static function gzipEncode($content)
	{
		if (!headers_sent()) {
			$config = Application::getInstance()->config;
			if (strlen($content) >= $config->get('output_gzip_minlen')) {
				$content = gzencode($content, $config->get('output_gzip_level'));
				header('Content-Encoding: gzip');
				header('Vary: Accept-Encoding');
				header('Content-Length: '.strlen($content));
			}
        }

        return $content;
	}

	protected static function isGzipSupport()
	{
		return (extension_loaded('zlib') && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false);
	}

}