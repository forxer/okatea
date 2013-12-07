<?php
/**
 * @ingroup okt_module_okatea_dot_org
 * @brief La classe principale du Module Okatea.org.
 *
 */

use Tao\Modules\Module;

class module_okatea_dot_org extends Module
{
	protected $sUrl;
	protected $sCacheFile;

	protected $aVersionInfo = array(
		'version' => null,
		'href' => null,
		'checksum' => null,
		'info' => null
	);

	protected $sCacheTtl = '-24 hours';

	protected function prepend()
	{
		$this->sRepositoryPath = realpath(__DIR__.'/../../../repository/');
	}

	protected function prepend_admin()
	{

	}

	protected function prepend_public()
	{

	}



	/**
	 * Retourne le chemin du template de l'encart de téléchargements.
	 *
	 * @return string
	 */
	public function getDownloadInsertTplPath()
	{
		//return 'okatea_dot_org/download_insert/'.$this->config->templates['download_insert']['default'].'/template';
		return 'okatea_dot_org/download_insert/default/template';
	}


	/*
	 *  retrieving latest releases versions
	 */

	public function getLatestStableVersionInfos()
	{
		return $this->getVersionInfo('stable');
	}

	public function getLatestDevVersionInfos()
	{
		return $this->getVersionInfo('dev');
	}

	protected function resetVersionInfos()
	{
		$this->aVersionInfo = array(
			'version' => null,
			'href' => null,
			'checksum' => null,
			'info' => null
		);
	}

	/**
	 * Récupération des informations de version sur le dépot distant.
	 *
	 */
	protected function getVersionInfo($sVersionType)
	{
		$this->resetVersionInfos();

		$sVersionType = ($sVersionType == 'dev'  ? 'dev' : 'stable');

		$this->sCacheFile = OKT_CACHE_PATH.'/releases/okatea-'.$sVersionType;

		# Check cached file
		if (is_readable($this->sCacheFile) && filemtime($this->sCacheFile) > strtotime($this->sCacheTtl))
		{
			$c = @file_get_contents($this->sCacheFile);
			$c = @unserialize($c);

			if (is_array($c))
			{
				$this->aVersionInfo = $c;
				return $this->aVersionInfo;
			}
		}

		$sCacheDir = dirname($this->sCacheFile);

		$bCanWrite = (!is_dir($sCacheDir) && is_writable(dirname($sCacheDir)))
			|| (!file_exists($this->sCacheFile) && is_writable($sCacheDir))
			|| is_writable($this->sCacheFile);

		# If we can't write file, don't bug host with queries
		if (!$bCanWrite) {
			return $this->aVersionInfo;
		}

		if (!is_dir($sCacheDir))
		{
			try {
				files::makeDir($sCacheDir);
			}
			catch (Exception $e) {
				return $this->aVersionInfo;
			}
		}

		# Try to get latest version number
		try
		{
			$sFilename = $this->sRepositoryPath.'/packages/versions.xml';

			if (!file_exists($sFilename)) {
				throw new Exception('File version.xml not found.');
			}

			$this->readVersion(file_get_contents($this->sRepositoryPath.'/packages/versions.xml'), $sVersionType);
		}
		catch (Exception $e) {
			return $this->aVersionInfo;
		}

		# Create cache
		file_put_contents($this->sCacheFile, serialize($this->aVersionInfo));

		return $this->aVersionInfo;
	}

	protected function readVersion($str, $sVersionType)
	{
		try
		{
			$xml = new SimpleXMLElement($str, LIBXML_NOERROR);
			$r = $xml->xpath("/versions/subject[@name='okatea']/release[@name='".$sVersionType."']");

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


} # class
