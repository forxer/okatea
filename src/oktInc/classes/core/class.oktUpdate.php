<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Orignal file from Dotclear 2.
 * Copyright (c) 2003-2013 Olivier Meunier & Association Dotclear
 * Licensed under the GPL version 2.0 license.
 */


/**
 * @class oktUpdate
 * @ingroup okt_classes_core
 * @brief Mise à jour automatisée d'Okatea
 *
 */
class oktUpdate
{
	const ERR_FILES_CHANGED = 101;
	const ERR_FILES_UNREADABLE = 102;
	const ERR_FILES_UNWRITALBE = 103;

	protected $sUrl;
	protected $sSubject;
	protected $sVersion;
	protected $sCacheFile;

	protected $aVersionInfo = array(
		'version' => null,
		'href' => null,
		'checksum' => null,
		'info' => null,
		'notify' => true
	);

	protected $sCacheTtl = '-6 hours';
	protected $aForcedFiles = array();

	/**
	 * Constructor.
	 *
	 * @param string $sUrl 			Versions file URL
	 * @param string $sSubject 		Subject to check
	 * @param string $sVersion 		Version type
	 * @param string $sCacheDir 	Directory cache path
	 */
	public function __construct($sUrl, $sSubject, $sVersion, $sCacheDir)
	{
		$this->sUrl = $sUrl;
		$this->sSubject = $sSubject;
		$this->sVersion = $sVersion;
		$this->sCacheFile = $sCacheDir.'/'.$sSubject.'-'.$sVersion;
	}

	/**
	 * Checks for Okatea updates.
	 * Returns latest version if available or false.
	 *
	 * @param version		string	Current version to compare
	 * @return string				Latest version if available
	 */
	public function check($sVersion)
	{
		$this->getVersionInfo();

		$v = $this->getVersion();

		if ($v && version_compare($sVersion, $v, '<')) {
			return $v;
		}

		return false;
	}

	/**
	 * Récupération des informations de version sur le dépot distant.
	 *
	 */
	public function getVersionInfo()
	{
		# Check cached file
		if (is_readable($this->sCacheFile) && filemtime($this->sCacheFile) > strtotime($this->sCacheTtl))
		{
			$c = @file_get_contents($this->sCacheFile);
			$c = @unserialize($c);

			if (is_array($c))
			{
				$this->aVersionInfo = $c;
				return;
			}
		}

		$sCacheDir = dirname($this->sCacheFile);

		$bCanWrite = (!is_dir($sCacheDir) && is_writable(dirname($sCacheDir)))
		|| (!file_exists($this->sCacheFile) && is_writable($sCacheDir))
		|| is_writable($this->sCacheFile);

		# If we can't write file, don't bug host with queries
		if (!$bCanWrite) {
			return;
		}

		if (!is_dir($sCacheDir))
		{
			try {
				files::makeDir($sCacheDir);
			} catch (Exception $e) {
				return;
			}
		}

		# Try to get latest version number
		try
		{
			$sPath = '';
			$oClient = netHttp::initClient($this->sUrl, $sPath);

			if ($oClient !== false)
			{
				$oClient->setTimeout(4);
				$oClient->setUserAgent($_SERVER['HTTP_USER_AGENT']);
				$oClient->get($sPath);

				$this->readVersion($oClient->getContent());
			}
		}
		catch (Exception $e) {}

		# Create cache
		file_put_contents($this->sCacheFile, serialize($this->aVersionInfo));
	}

	public function getVersion()
	{
		return $this->aVersionInfo['version'];
	}

	public function getFileURL()
	{
		return $this->aVersionInfo['href'];
	}

	public function getInfoURL()
	{
		return $this->aVersionInfo['info'];
	}

	public function getChecksum()
	{
		return $this->aVersionInfo['checksum'];
	}

	public function getNotify()
	{
		return $this->aVersionInfo['notify'];
	}

	public function getForcedFiles()
	{
		return $this->aForcedFiles;
	}

	public function setForcedFiles()
	{
		$this->aForcedFiles = func_get_args();
	}

	public function setForcedFile($sFile)
	{
		$this->aForcedFiles[] = $sFile;
	}

	/**
	 * Sets notification flag.
	 *
	 * @param boolean $n
	 */
	public function setNotify($n)
	{
		if (!is_writable($this->sCacheFile)) {
			return;
		}

		$this->aVersionInfo['notify'] = (boolean)$n;
		file_put_contents($this->sCacheFile, serialize($this->aVersionInfo));
	}

	/**
	 * Check installation integrity.
	 *
	 * @param unknown_type $sDigestsFile
	 * @param unknown_type $sRoot
	 * @throws Exception
	 * @return boolean
	 */
	public function checkIntegrity($sDigestsFile, $sRoot)
	{
		if (!$sDigestsFile) {
			throw new Exception(__('c_a_update_digests_not_found'));
		}

		$aChanges = $this->md5sum($sRoot, $sDigestsFile);

		if (!empty($aChanges))
		{
			$e = new Exception('Some files have changed.', self::ERR_FILES_CHANGED);
			$e->bad_files = $aChanges;
			throw $e;
		}

		return true;
	}

	/**
	 * Downloads new version to destination $sDest.
	 *
	 * @param string $sDest
	 * @throws Exception
	 */
	public function download($sDest)
	{
		$sUrl = $this->getFileURL();

		if (!$sUrl) {
			throw new Exception(__('c_a_update_no_file_to_download'));
		}

		if (!is_writable(dirname($sDest))) {
			throw new Exception(__('c_a_update_root_directory_not_writable'));
		}

		try
		{
			$oClient = netHttp::initClient($sUrl, $sPath);
			$oClient->setTimeout(4);
			$oClient->setUserAgent($_SERVER['HTTP_USER_AGENT']);
			$oClient->useGzip(false);
			$oClient->setPersistReferers(false);
			$oClient->setOutput($sDest);
			$oClient->get($sPath);

			if ($oClient->getStatus() != 200)
			{
				@unlink($sDest);
				throw new Exception();
			}
		}
		catch (Exception $e)
		{
			throw new Exception(__('c_a_update_error_occurred_while_downloading'));
		}
	}

	/**
	 * Checks if archive was successfully downloaded.
	 *
	 * @param string $sZip
	 * @return boolean
	 */
	public function checkDownload($sZip)
	{
		$cs = $this->getChecksum();

		return ($cs && is_readable($sZip) && md5_file($sZip) == $cs);
	}

	/**
	 * Backups changed files before an update.
	 *
	 * @param string $sZipFile
	 * @param string $sZipDigests
	 * @param string $sRoot
	 * @param string $sRootDigests
	 * @param string $sDest
	 * @throws Exception
	 * @return boolean
	 */
	public function backup($sZipFile, $sZipDigests, $sRoot, $sRootDigests, $sDest)
	{
		if (!is_readable($sZipFile)) {
			throw new Exception(__('c_a_update_archive_not_found'));
		}

		if (!is_readable($sRootDigests)) {
			@unlink($sZipFile);
			throw new Exception(__('c_a_update_unable_read_digests'));
		}

		# Stop everything if a backup already exists and can not be overrided
		if (!is_writable(dirname($sDest)) && !file_exists($sDest)) {
			throw new Exception(__('c_a_update_root_directory_not_writable'));
		}

		if (file_exists($sDest) && !is_writable($sDest)) {
			return false;
		}

		$b_fp = @fopen($sDest,'wb');
		if ($b_fp === false) {
			return false;
		}

		$oZip = new fileUnzip($sZipFile);
		$b_zip = new fileZip($b_fp);

		if (!$oZip->hasFile($sZipDigests))
		{
			@unlink($sZipFile);
			throw new Exception(__('c_a_update_downloaded_file_not_valid_archive'));
		}

		$opts = FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES;
		$cur_digests = file($sRootDigests, $opts);
		$new_digests = explode("\n",$oZip->unzip($sZipDigests));
		$aNewFiles = $this->getNewFiles($cur_digests, $new_digests);
		$oZip->close();
		unset($opts, $cur_digests, $new_digests, $oZip);

		$aNotReadable = array();

		if (!empty($this->aForcedFiles)) {
			$aNewFiles = array_merge($aNewFiles, $this->aForcedFiles);
		}

		foreach ($aNewFiles as $file)
		{
			if (!$file || !file_exists($sRoot.'/'.$file)) {
				continue;
			}

			try {
				$b_zip->addFile($sRoot.'/'.$file, $file);
			} catch (Exception $e) {
				$aNotReadable[] = $file;
			}
		}

		# If only one file is not readable, stop everything now
		if (!empty($aNotReadable))
		{
			$e = new Exception('Some files are not readable.', self::ERR_FILES_UNREADABLE);
			$e->bad_files = $aNotReadable;
			throw $e;
		}

		$b_zip->write();
		fclose($b_fp);
		$b_zip->close();

		return true;
	}

	/**
	 * Upgrade process.
	 *
	 * @param string $sZipFile
	 * @param string $sZipDigests
	 * @param string $sZipRoot
	 * @param string $sRoot
	 * @param string $sRootDigests
	 * @throws Exception
	 */
	public function performUpgrade($sZipFile, $sZipDigests, $sZipRoot, $sRoot, $sRootDigests)
	{
		if (!is_readable($sZipFile)) {
			throw new Exception(__('Archive not found.'));
		}

		if (!is_readable($sRootDigests)) {
			@unlink($sZipFile);
			throw new Exception(__('Unable to read current digests file.'));
		}

		$oZip = new fileUnzip($sZipFile);

		if (!$oZip->hasFile($sZipDigests))
		{
			@unlink($sZipFile);
			throw new Exception(__('Downloaded file does not seem to be a valid archive.'));
		}

		# force /install dir
		foreach ($oZip->getFilesList() as $sFile)
		{
			$sFile = str_replace($sZipRoot.'/', '', $sFile);

			if (substr($sFile, 0, 7) == 'install') {
				$this->setForcedFile($sFile);
			}
		}

		$opts = FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES;
		$cur_digests = file($sRootDigests, $opts);
		$new_digests = explode("\n", $oZip->unzip($sZipDigests));
		$aNewFiles = self::getNewFiles($cur_digests, $new_digests);

		if (!empty($this->aForcedFiles)) {
			$aNewFiles = array_merge($aNewFiles, $this->aForcedFiles);
		}

		$aZipFiles = array();
		$aNotWritable = array();

		foreach ($aNewFiles as $file)
		{
			if (!$file) {
				continue;
			}

			if (!$oZip->hasFile($sZipRoot.'/'.$file)) {
				@unlink($sZipFile);
				throw new Exception(__('c_a_update_incomplete_archive'));
			}

			$sDest = $sDest_dir = $sRoot.'/'.$file;
			while (!is_dir($sDest_dir = dirname($sDest_dir)));

			if ((file_exists($sDest) && !is_writable($sDest)) || (!file_exists($sDest) && !is_writable($sDest_dir)))
			{
				$aNotWritable[] = $file;
				continue;
			}

			$aZipFiles[] = $file;
		}

		# If only one file is not writable, stop everything now
		if (!empty($aNotWritable))
		{
			$e = new Exception('Some files are not writable', self::ERR_FILES_UNWRITALBE);
			$e->bad_files = $aNotWritable;
			throw $e;
		}

		# Everything's fine, we can write files, then do it now
		$can_touch = function_exists('touch');
		foreach ($aZipFiles as $file)
		{
			$oZip->unzip($sZipRoot.'/'.$file, $sRoot.'/'.$file);

			if ($can_touch) {
				@touch($sRoot.'/'.$file);
			}
		}

		@unlink($sZipFile);
	}

	protected function getNewFiles($cur_digests, $new_digests)
	{
		$cur_md5 = $cur_path = $cur_digests;
		$new_md5 = $new_path = $new_digests;

		array_walk($cur_md5, array($this,'parseLine'),1);
		array_walk($cur_path,array($this,'parseLine'),2);
		array_walk($new_md5, array($this,'parseLine'),1);
		array_walk($new_path,array($this,'parseLine'),2);

		$cur = array_combine($cur_md5,$cur_path);
		$new = array_combine($new_md5,$new_path);

		return array_values(array_diff_key($new,$cur));
	}

	protected function readVersion($str)
	{
		try
		{
			$xml = new SimpleXMLElement($str,LIBXML_NOERROR);
			$r = $xml->xpath("/versions/subject[@name='".$this->sSubject."']/release[@name='".$this->sVersion."']");

			if (!empty($r) && is_array($r))
			{
				$r = $r[0];
				$this->aVersionInfo['version'] = isset($r['version']) ? (string) $r['version'] : null;
				$this->aVersionInfo['href'] = isset($r['href']) ? (string) $r['href'] : null;
				$this->aVersionInfo['checksum'] = isset($r['checksum']) ? (string) $r['checksum'] : null;
				$this->aVersionInfo['info'] = isset($r['info']) ? (string) $r['info'] : null;
			}
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}

	protected function md5sum($sRoot, $sDigestsFile)
	{
		if (!is_readable($sDigestsFile)) {
			throw new Exception(__('c_a_update_unable_read_digests'));
		}

		$opts = FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES;
		$contents = file($sDigestsFile,$opts);

		$changes = array();

		foreach ($contents as $digest)
		{
			if (!preg_match('#^([\da-f]{32})\s+(.+?)$#',$digest,$m)) {
				continue;
			}

			$md5 = $m[1];
			$filename = $sRoot.'/'.$m[2];

			# Invalid checksum
			if (!is_readable($filename) || !self::md5_check($filename, $md5)) {
				$changes[] = substr($m[2],2);
			}
		}

		# No checksum found in digests file
		if (empty($md5)) {
			throw new Exception(__('c_a_update_invalid_digests'));
		}

		return $changes;
	}

	protected function parseLine(&$v, $k, $n)
	{
		if (!preg_match('#^([\da-f]{32})\s+(.+?)$#',$v,$m)) {
			return;
		}

		$v = $n == 1 ? md5($m[2].$m[1]) : substr($m[2],2);
	}

	protected static function md5_check($filename, $md5)
	{
		if (md5_file($filename) === $md5) {
			return true;
		}
		else
		{
			$filecontent = file_get_contents($filename);
			$filecontent = str_replace("\r\n", "\n", $filecontent);
			$filecontent = str_replace("\r", "\n", $filecontent);

			if (md5($filecontent) === $md5) {
				return true;
			}
		}

		return false;
	}


	public static function dbUpdate($oChecklist=null)
	{
		global $okt;

		if (empty($okt) || !($okt instanceof oktCore)) {
			return false;
		}

		if (is_null($oChecklist) || !($oChecklist instanceof checkList)) {
			$oChecklist = new checkList();
		}

		foreach (new DirectoryIterator(OKT_INC_PATH.'/sql_schema/') as $oFileInfo)
		{
			if ($oFileInfo->isDot() || !$oFileInfo->isFile() || $oFileInfo->getExtension() !== 'xml') {
				continue;
			}

			$xsql = new xmlsql($okt->db, file_get_contents($oFileInfo->getPathname()), $oChecklist, 'update');
			$xsql->replace('{{PREFIX}}',OKT_DB_PREFIX);
			$xsql->execute();
		}
	}

} # class
