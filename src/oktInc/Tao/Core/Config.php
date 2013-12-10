<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Core;

use Symfony\Component\Yaml\Yaml;

/**
 * Gestion des fichiers de configuration.
 *
 */
class Config
{
	/**
	 * Le chemin du fichier source
	 * @var string
	 */
	protected $sSourceFile;

	/**
	 * L'objet de mise en cache
	 * @var object
	 */
	protected $oCache = null;

	/**
	 * L'identifiant du cache
	 * @var string
	 */
	protected $sCacheId;

	/**
	 * Les données
	 * @var array
	 */
	protected $aData;


	/**
	 * Constructeur. Charge les données.
	 *
	 * @param SingleFileCache $oCache
	 * @param string $sSourceFile
	 * @return void
	 */
	public function __construct($oCache, $sSourceFile)
	{
		$this->oCache = $oCache;

		$this->sSourceFile = $sSourceFile.'.yaml';
		$this->sCacheId = basename($sSourceFile);

		$this->loadData();
	}

	public function __get($sName)
	{
		return $this->getData($sName);
	}

	public function __set($sName, $mValue)
	{
		$this->aData[$sName] = $mValue;
	}

	public function __isset($sName)
	{
		return isset($this->aData[$sName]);
	}

	public function __unset($sName)
	{
		unset($this->aData[$sName]);
	}

	public function get($sName=null)
	{
		if ($sName === null) {
			return $this->aData;
		}

		return $this->getData($sName);
	}

	public function getData($sName)
	{
		if (isset($this->aData[$sName])) {
			return $this->aData[$sName];
		}

		trigger_error('There is no config data for '.$sName.' key.', E_USER_NOTICE);

		return null;
	}

	private function loadData()
	{
		if (!$this->oCache->contains($this->sCacheId)) {
			$this->generateCacheFile();
		}

		$this->aData = $this->oCache->fetch($this->sCacheId);
	}

	private function loadSource()
	{
		try {
			return Yaml::parse($this->sSourceFile);
		}
		catch (Exception $e)
		{
			$trace = debug_backtrace();
			trigger_error(
				'Problème lecture configuration : ' . $e->getMessage() .
				' dans ' . $trace[0]['file'] . ' à la ligne ' . $trace[0]['line'],
				E_USER_WARNING);

			return array();
		}
	}

	private function generateCacheFile()
	{
		return $this->oCache->save($this->sCacheId, $this->loadSource());
	}

	public function write($aData)
	{
		$aData = array_merge($this->loadSource(), $aData);

		file_put_contents($this->sSourceFile, Yaml::dump($aData));

		$this->generateCacheFile();
	}

	public function writeCurrent()
	{
		file_put_contents($this->sSourceFile, Yaml::dump($this->aData));

		$this->generateCacheFile();
	}

	public function merge()
	{
		$aData = array_merge($this->oCache->fetch($this->sCacheId), $this->loadSource());

		file_put_contents($this->sSourceFile, Yaml::dump($aData));

		$this->generateCacheFile();
	}

}
