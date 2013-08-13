<?php

//namespace Doctrine\Common\Cache;

/**
 * File cache driver.
 *
 * @author Peeter Tomberg <peeter.tomberg@gmail.com>
 */
class FileCache extends AbstractCache
{
	/**
	 * Location of the cache directory
	 * @var string
	 */
	private $dir;

	/**
	 * Construct the file cache
	 * @param string $dir - the location where the cache files will be stored
	 */
	public function __construct($dir)
	{
		$this->dir = $dir;

		if (!is_dir($this->dir)) {
			if (!mkdir($this->dir)) {
				die('Unable to create the file cache directory at ' . $this->dir);
			}
		}
	}

	/**
	 * Formats the key into a hash to prevent filename errors
	 * @param string $id
	 */
	private function hashName($id)
	{
		return sprintf("%s/%s", $this->dir , $this->String2Hex($id));
	}

	/**
	 * Format a string into hex
	 * @param unknown_type $string
	 */
	private function String2Hex($string)
	{
		$hex = '';

		for ($i=0; $i < strlen($string); $i++) {
			$hex .= dechex(ord($string[$i]));
		}

		return $hex;
	}

	private function Hex2String($hex)
	{
		$string = '';

		for ($i=0; $i < strlen($hex)-1; $i+=2) {
			$string .= chr(hexdec($hex[$i].$hex[$i+1]));
		}

		return $string;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getIds()
	{
		$keys = array();

		$files = glob($this->dir. "*" . '.php');

		foreach ($files as $file)
		{
			$filename = substr($file, strlen($this->dir));
			$keys[] = $this->Hex2String(substr($filename, 0, strpos($filename, " - ")));
		}

		return $keys;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _doFetch($id)
	{
		if (!is_dir($this->dir) OR !is_writable($this->dir)) {
			return false;
		}

		if (!$this->_doContains($id)) {
			return false;
		}

		$files = glob($this->hashName($id) . ' - ' . "*" . '.php');
		$cache_path = end($files);

		if (!@file_exists($cache_path)) {
			return false;
		}

		if (!$fp = @fopen($cache_path, 'rb')) {
			return false;
		}

		flock($fp, LOCK_SH);
		$cache = '';

		if (filesize($cache_path) > 0) {
			$cache = unserialize(fread($fp, filesize($cache_path)));
		}
		else {
			$cache = NULL;
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		return $cache;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _doContains($id)
	{
		$files = glob($this->hashName($id) . ' - ' . "*" . '.php');

		if (count($files) < 1) {
			return false;
		}

		$cache_path = end($files);

		$filename = substr($cache_path, strlen($this->dir));
		$expiration = substr($filename, strpos($filename, " - ")+2, -4);

		if ($expiration > 0 && filemtime($cache_path) < (time() - $expiration))
		{
			$this->_doDelete($id);
			return false;
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _doSave($id, $data, $lifeTime = 0)
	{
		if (!is_dir($this->dir) OR !is_writable($this->dir)) {
			return false;
		}

		if ($this->_doContains($id)) {
			$this->_doDelete($id);
		}

		$cache_path = $this->hashName($id) . ' - ' . (($lifeTime > 0 ? time() : 0) + $lifeTime) . '.php';

		if (!$fp = fopen($cache_path, 'wb')) {
			return false;
		}

		if (flock($fp, LOCK_EX))
		{
			fwrite($fp, serialize($data));
			flock($fp, LOCK_UN);
		}
		else {
			return false;
		}

		fclose($fp);
		@chmod($cache_path, 0777);

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _doDelete($id)
	{
		# Remove all files matching the keyword in the cache
		$files = glob($this->hashName($id) . ' - ' . "*" . '.php');

		foreach ($files as $cache_path)
		{
			if (file_exists($cache_path)) {
				unlink($cache_path);
			}
		}

		if (count($files) > 0) {
			return true;
		}

		return false;
	}

}
