<?php
/**
 * Created by PhpStorm.
 * User: pader
 * Date: 2017/7/11
 * Time: 14:00
 */

namespace vgot\Core;

class Security
{

	protected $encryptMethod = 'aes-128-cbc';
	protected $password;

	public function __construct($password, $encryptMethod=null)
	{
		$this->password = $password;

		if ($encryptMethod !== null) {
			if (!in_array($encryptMethod, openssl_get_cipher_methods())) {
				throw new \ErrorException("Unsupport method '$encryptMethod' for secruity.");
			}
			$this->encryptMethod = $encryptMethod;
		}
	}

	/**
	 * Encrypt data
	 *
	 * Use (openssl encrypt + random iv + md5 signature) to keep data safe
	 *
	 * @param string $data
	 * @param bool $urlSafe
	 * @return mixed|string
	 */
	public function encrypt($data, $urlSafe=false)
	{
		$ivl = openssl_cipher_iv_length($this->encryptMethod);
		$iv = openssl_random_pseudo_bytes($ivl);

		$data = openssl_encrypt($data, $this->encryptMethod, $this->password, OPENSSL_RAW_DATA, $iv);
		$data .= $iv;
		$data .= md5($data, true);

		$urlSafe && $data = self::urlSafeBase64Encode($data);

		return $data;
	}

	/**
	 * Decrypt data
	 *
	 * @param string $data
	 * @param bool $urlSafe
	 * @return bool|string
	 * @see encrypt
	 */
	public function decrypt($data, $urlSafe=false)
	{
		$urlSafe && $data = self::urlSafeBase64Decode($data);

		if (!$data) {
			return false;
		}

		$ivl = openssl_cipher_iv_length($this->encryptMethod);
		$sign = substr($data, -16);
		$data = substr($data, 0, -16);

		//验证签名
		if (strcmp($sign, md5($data, true)) !== 0) {
			return false;
		}

		$iv = substr($data, -$ivl);
		$data = substr($data, 0, -$ivl);

		return openssl_decrypt($data, $this->encryptMethod, $this->password, OPENSSL_RAW_DATA, $iv);
	}

	/**
	 * URL 安全的 base64 编码
	 *
	 * @param string $string
	 * @return mixed
	 */
	public static function urlSafeBase64Encode($string)
	{
		$data = base64_encode($string);
		return str_replace(['+','/','='],['-','_',''], $data);
	}

	/**
	 * 对 URL 安全的 base64 编码进行解码
	 *
	 * @param string $string
	 * @return bool|string
	 */
	public static function urlSafeBase64Decode($string)
	{
		$data = str_replace(['-','_'], ['+','/'], $string);
		$mod = strlen($data) % 4;
		$mod > 0 && $data .= substr('====', $mod);
		return base64_decode($data);
	}

}