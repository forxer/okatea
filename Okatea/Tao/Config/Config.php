<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Config;

use Okatea\Tao\Cache\SingleFileCache;
use Symfony\Component\Yaml\Yaml;

/**
 * Management of configuration files.
 */
class Config
{
	/**
	 * The path of the source file.
	 *
	 * @var string
	 */
	protected $sSourceFile;

	/**
	 * Single file cache instance.
	 *
	 * @var Okatea\Tao\Cache\SingleFileCache
	 */
	protected $oCache = null;

	/**
	 * The cache identifier.
	 *
	 * @var string
	 */
	protected $sCacheId;

	/**
	 * The config data.
	 *
	 * @var array
	 */
	protected $aData = [];

	/**
	 * Constructor. Load data.
	 *
	 * @param SingleFileCache $oCache
	 * @param string $sSourceFile
	 * @return void
	 */
	public function __construct(SingleFileCache $oCache, $sSourceFile)
	{
		$this->oCache = $oCache;

		$this->sSourceFile = $sSourceFile . '.yml';
		$this->sCacheId = basename($sSourceFile);

		$this->loadData();
	}

	/**
	 * Return a given data.
	 *
	 * @param string $sName
	 * @return mixed
	 */
	public function __get($sName)
	{
		return $this->getData($sName);
	}

	/**
	 * Set a given data.
	 *
	 * @param string $sName
	 * @param mixed $mValue
	 * @return void
	 */
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

	public function get($sName = null)
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

		trigger_error('There is no config data for ' . $sName . ' key.', E_USER_NOTICE);

		return null;
	}

	protected function loadData()
	{
		if (!$this->oCache->contains($this->sCacheId)) {
			$this->generateCacheFile();
		}

		$this->aData = $this->oCache->fetch($this->sCacheId);
	}

	protected function loadSource()
	{
		return Yaml::parse(file_get_contents($this->sSourceFile));
	}

	protected function generateCacheFile()
	{
		return $this->oCache->save($this->sCacheId, $this->loadSource());
	}

	public function write($aData)
	{
		$aData = array_merge($this->loadSource(), $aData);

		file_put_contents($this->sSourceFile, Yaml::dump($aData, 4));

		$this->generateCacheFile();
	}

	public function writeCurrent()
	{
		file_put_contents($this->sSourceFile, Yaml::dump($this->aData, 4));

		$this->generateCacheFile();
	}

	public function merge()
	{
		$aData = array_merge($this->oCache->fetch($this->sCacheId), $this->loadSource());

		file_put_contents($this->sSourceFile, Yaml::dump($aData));

		$this->generateCacheFile();
	}
}
