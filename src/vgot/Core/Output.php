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

	protected $mode = null;
	protected $charset = 'utf-8';

	public function __construct()
	{
		$config = Application::getInstance()->config;

		//charset
		$charset = $config->get('output_charset');
		$charset && $this->charset = $charset;

		//gzip
		if ($config->get('output_gzip') &&
			($config->get('output_gzip_force_soft') || !$this->enableHardGzip())
		) {
			ob_start('\\' . self::class . '::gzipEncode');
			$this->mode = 'soft';
		} else {
			ob_start();
		}

		ob_implicit_flush(false);
	}

	public function __destruct()
	{
		if (ob_get_level()) {
			ob_end_flush();
		}
	}

	public function flush()
	{
		ob_flush();
		flush();
	}

	public function getBuffer()
	{
		return ob_get_contents();
	}

	public function getMode()
	{
		return $this->mode;
	}

	protected function enableHardGzip()
	{
		if (!headers_sent()) {
			if (ini_set('zlib.output_compression', 4096) !== false) {
				ini_set('zlib.output_compression_level', Application::getInstance()->config->get('output_gzip_level'));
			} elseif (!ob_start('ob_gzhandler')) {
				return false;
			}

			$this->mode = 'hard';
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

	public function json($data, $charset=null)
	{
		$charset = $charset ?: $this->charset;
		header('Content-Type: application/json; charset='.$charset);
		echo json_encode($data);
	}

}