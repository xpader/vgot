<?php
/**
 * Created by PhpStorm.
 * User: Pader
 * Date: 2018/4/25
 * Time: 22:17
 */

namespace vgot\Web;


use vgot\Exceptions\ApplicationException;

class Uploader {

	/**
	 * Upload file field name in post form
	 * @var string
	 */
	public $fieldName = 'file';

	/**
	 * Allow upload file extensions
	 *
	 * '*' for all file
	 * 'jpg' for only .jpg extension file
	 * 'gif,jpeg,jpg,png,webp' for all webpage images file
	 *
	 * @var string
	 */
	public $allowExtensions = '*';

	/**
	 * Upload filesize limit bytes
	 *
	 * 0 for no limit, 500 mean 500bytes, also support unit KB,MB,GB.
	 * example: '200KB', '5MB', '1GB'.
	 *
	 * @var int
	 */
	public $filesizeLimit = 0;

	/**
	 * Dir to save (for save())
	 * @var string
	 */
	public $saveDir = '.';

	/**
	 * Auto mkdir for save()
	 *
	 * When $autoMkdir = true, and saved dir is not exists, the Uploader will try to make target directory.
	 * If $autoMkdir = false, or make directory failed, the upload will be failed.
	 *
	 * @var bool
	 */
	public $autoMkdir = true;

	/**
	 * Auto mkdir mode
	 * @var int
	 */
	public $dirMode = 0755;

	/**
	 * Upload file mode
	 * @var int
	 */
	public $fileMode = 0664;

	/**
	 * Overwrite file if save path exists
	 * @var bool
	 */
	public $overwrite = false;

	/**
	 * When call saveAs() or use $autoName, is file extension be overwrite.
	 * @var bool
	 */
	public $overwriteExtension = false;

	/**
	 * When save() file, auto generate a file name to destination path.
	 * This will ignore $overwrite setting.
	 * @var bool
	 */
	public $autoName = true;

	/**
	 * Instated space use '_' when $autoName is true.
	 * @var bool
	 */
	public $removeSpace = true;

	protected $result;
	protected $files;
	protected $validateStatus;
	protected $current;
	protected $info;

	public function __construct($config=[])
	{
		if ($config) {
			configClass($this, $config);
		}
	}

	/**
	 * Fetch multiple file index
	 *
	 * @return bool|mixed
	 */
	public function fetch()
	{
		if (!$this->validate()) {
			return false;
		}

		if (current($this->files)) {
			$this->current = key($this->files);
			next($this->files);
			return $this->current;
		} else {
			return false;
		}
	}

	/**
	 * Reset fetch current
	 */
	public function reset()
	{
		reset($this->files);
	}

	/**
	 * Validate upload
	 *
	 * @return true|int
	 */
	public function validate()
	{
		if ($this->validateStatus !== null) {
			return $this->validateStatus;
		}

		$result = [];

		foreach ($this->getFilesArray() as $i => $file) {
			$err = $this->validateFile($file);
			if ($err != UPLOAD_ERR_OK) {
				$result[$i] = $err;
			}
		}

		$this->reset();

		$this->result = $result;
		return $this->validateStatus = count($result) == 0;
	}

	/**
	 * Save file to dir
	 *
	 * @param string|null $dir
	 * @return bool
	 */
	public function save($dir=null)
	{
		if (!$this->validate()) {
			return false;
		}

		$file = $this->files[$this->current];

		if ($dir === null) {
			$dir = $this->saveDir;
		}

		if (!is_dir($dir) && !mkdir($dir, $this->dirMode, true)) {
			return false;
		}

		$pathinfo = pathinfo($file['name']);
		$ext = isset($pathinfo['extension']) ? strtolower($pathinfo['extension']) : '';

		//generate a unique filename when autoName is true
		do {
			$filename = $this->autoName ? uniqid() :
				($this->removeSpace ? str_replace(' ', '_', $pathinfo['filename']) : $pathinfo['filename']);

			if (!$this->overwriteExtension && $ext) {
				$filename .= '.'.$ext;
			}

			$savePath = $dir.'/'.$filename;

		} while ($this->autoName && is_file($savePath));

		//if (!$this->autoName && is_file($savePath) && !unlink($savePath)) {
		//	$this->result[$this->current] = UPLOAD_ERR_CANT_WRITE;
		//	return false;
		//}

		if (!$this->autoName && !$this->overwrite && is_file($savePath)) {
			$this->result[$this->current] = UPLOAD_ERR_CANT_WRITE;
			return false;
		}

		$this->info = [
			'type' => $file['type'],
			'extension' => $ext,
			'filename' => $file['name'],
			'size' => $file['size'],
			'realpath' => $savePath,
			'savename' => $filename
		];

		return $this->move($file['tmp_name'], $savePath);
	}

	/**
	 * Save file to special path
	 *
	 * @param string $dest
	 * @param bool $overwrite
	 * @return bool
	 */
	public function saveAs($dest, $overwrite=true)
	{
		if (!$this->validate()) {
			return false;
		}

		$file = $this->files[$this->current];

		if (!$overwrite && is_file($dest)) {
			$this->result[$this->current] = UPLOAD_ERR_CANT_WRITE;
			return false;
		}

		$pathinfo = pathinfo($dest);

		$this->info = [
			'type' => $file['type'],
			'extension' =>  isset($pathinfo['extension']) ? strtolower($pathinfo['extension']) : '',
			'filename' => $file['name'],
			'size' => $file['size'],
			'realpath' => $dest,
			'savename' => $pathinfo['basename']
		];

		return $this->move($file['tmp_name'], $dest);
	}

	protected function move($tmp, $dest)
	{
		if (move_uploaded_file($tmp, $dest)) {
			chmod($dest, $this->fileMode);
			return true;
		} else {
			$this->result[$this->current] = UPLOAD_ERR_CANT_WRITE;
			return false;
		}
	}

	/**
	 * Get current uploaded file info
	 * @return array
	 */
	public function getUploadedInfo()
	{
		return $this->info;
	}

	/**
	 * Is it multiple files upload
	 * @return bool
	 */
	public function isMultiUpload()
	{
		$files = $this->getFiles();
		return $files ? is_array($files['name']) : false;
	}

	/**
	 * Get multiple upload as standard 2D array
	 *
	 * @return array|null
	 */
	protected function getFilesArray()
	{
		if ($this->files === null) {
			$files = $this->getFiles();

			if ($this->isMultiUpload()) {
				$arr = [];
				foreach (array_keys($files['name']) as $i) {
					foreach ($files as $k => $list) {
						$arr[$i][$k] = $list[$i];
					}
				}

				//ignore no file input
				$arr2 = [];
				foreach ($arr as $i => $file) {
					if ($file['error'] == UPLOAD_ERR_NO_FILE) {
						continue;
					}
					$arr2[$i]  = $file;
				}

				$this->files = $arr2;
			} else {
				$this->files = [$files];
			}

			$this->current = key($this->files);
		}

		return $this->files;
	}

	/**
	 * @return null|array
	 */
	protected function getFiles()
	{
		if (!isset($_FILES[$this->fieldName])) {
			return null;
		}

		return $_FILES[$this->fieldName];
	}

	/**
	 * Validate file is allowed for upload
	 *
	 * @param array $file
	 * @return int
	 */
	protected function validateFile($file)
	{
		if (!$file) {
			return UPLOAD_ERR_NO_FILE;
		}

		if ($file['error'] != 0) {
			return $file['error'];
		}

		if (!is_uploaded_file($file['tmp_name'])) {
			return UPLOAD_ERR_NO_FILE;
		}

		if ($this->filesizeLimit > 0 && $file['size'] > $this->getLimitBytes()) {
			return UPLOAD_ERR_FORM_SIZE;
		}

		$extension = pathinfo($file['name'], PATHINFO_EXTENSION);

		if(!$this->checkExtension($extension)) {
			return UPLOAD_ERR_EXTENSION;
		}

		return UPLOAD_ERR_OK;
	}

	/**
	 * Get configure filesizeLimit to bytes unit
	 * @return int
	 * @throws ApplicationException
	 */
	protected function getLimitBytes()
	{
		if ($this->filesizeLimit == 0) {
			return $this->filesizeLimit;
		} elseif (preg_match('/^(\d+)([KMGT])B?$/i', $this->filesizeLimit, $m)) {
			switch (strtoupper($m[2])) {
				case 'T': $m[1] *= 1024;
				case 'G': $m[1] *= 1024;
				case 'M': $m[1] *= 1024;
				case 'K': $m[1] *= 1024;
			}
			return $m[1];
		} else {
			throw new ApplicationException("Unexcept 'filesizeLimit' setting: {$this->filesizeLimit}.");
		}
	}

	/**
	 * Check file extension
	 *
	 * @param string $extension
	 * @return bool
	 */
	private function checkExtension($extension)
	{
		if ($this->allowExtensions != '*') {
			$exts = explode(',', $this->allowExtensions);
			$exts = array_map('trim', $exts);
			$extension = strtolower($extension);
			return in_array($extension, $exts);
		}
		return true;
	}

	/**
	 * Get current upload error
	 * @return array|null
	 */
	public function getError()
	{
		$errors = $this->getErrors();
		return isset($errors[$this->current]) ? $errors[$this->current] : null;
	}

	/**
	 * Get multiple upload errors
	 * @return array
	 */
	public function getErrors()
	{
		$errors = [];

		foreach ($this->result as $i => $code) {
			$errors[$i] = [
				'code' => $code,
				'message' => $this->getErrorMessage($code),
				'file' => $this->files[$i]
			];
		}

		return $errors;
	}

	/**
	 * Get error message text
	 *
	 * @param int $errcode
	 * @return string
	 */
	public function getErrorMessage($errcode)
	{
		switch ($errcode) {
			case UPLOAD_ERR_OK: return 'no error'; break;
			case UPLOAD_ERR_EXTENSION: return 'upload file type is not allowed.'; break;
			case UPLOAD_ERR_FORM_SIZE: return 'upload file is exceed limit size.'; break;
			case UPLOAD_ERR_NO_FILE: return 'no file upload.'; break;
			case UPLOAD_ERR_CANT_WRITE: return 'server can not write file.'; break;
			case UPLOAD_ERR_INI_SIZE: return 'upload file is exceed server limit size.'; break;
			case UPLOAD_ERR_NO_TMP_DIR: return 'no temp dir'; break;
			case UPLOAD_ERR_PARTIAL: return 'upload file is in partial.'; break;
			default: return 'unknow error.';
		}
	}

}