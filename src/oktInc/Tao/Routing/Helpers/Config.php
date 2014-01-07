<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Routing\Helpers;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class Config
{
	protected $okt;
	protected $sPath;
	protected $aCollection;
	protected $bUniqueLanguage;

	protected $oFiles;
	protected $aLoadedRoutes;
	protected $aRoutesFromFiles;

	public function __construct($okt, $sPath, $aCollection)
	{
		$this->okt = $okt;
		$this->sPath = $sPath;
		$this->aCollection = $aCollection;
	}

	public function getRoutesInfos()
	{
		$aLoadedRoutes = $this->getLoadedRoutes();
		$aRoutesFromFiles = $this->getRoutesFromFiles();

		$aRoutesInfos = array();
		foreach ($aRoutesFromFiles as $sName=>$aRoute)
		{
			$aRoutesInfos[$sName] = array_merge($this->getEmptyRoute(), $aRoutesFromFiles[$sName]);

			$aRoutesInfos[$sName]['loaded'] = null;
			$aRoutesInfos[$sName]['controller'] = $aRoutesInfos[$sName]['defaults']['_controller'];
			unset($aRoutesInfos[$sName]['defaults']['_controller']);
		}

		uasort($aRoutesInfos, function($a, $b) {
			return strcasecmp($a['file'], $b['file']);
		});

		return $aRoutesInfos;
	}

	/**
	 * Return routes currently loaded by the system.
	 *
	 * @return array
	 */
	public function getLoadedRoutes()
	{
		if ($this->aLoadedRoutes !== null) {
			return $this->aLoadedRoutes;
		}

		$this->aLoadedRoutes = array();

		foreach ($this->aCollection as $sName=>$oRoute)
		{
			$this->aLoadedRoutes[$sName] = array(
				'path' => $oRoute->getPath(),
				'defaults' => $oRoute->getDefaults(),
				'requirements' => $oRoute->getRequirements(),
				'options' => $oRoute->getOptions(),
				'host' => $oRoute->getHost(),
				'schemes' => $oRoute->getSchemes(),
				'methods' => $oRoute->getMethods(),
				'condition' => $oRoute->getCondition()
			);
		}

		return $this->aLoadedRoutes;
	}

	/**
	 * Return routes from yaml files.
	 *
	 * @return array
	 */
	public function getRoutesFromFiles()
	{
		if ($this->aRoutesFromFiles !== null) {
			return $this->aRoutesFromFiles;
		}

		$oRoutesFiles = $this->getFiles();

		if (empty($oRoutesFiles)) {
			return null;
		}

		$this->aRoutesFromFiles = array();

		foreach ($oRoutesFiles as $oFile)
		{
			$aRoutes = Yaml::parse(file_get_contents($oFile->getPathname()));

			foreach ($aRoutes as $sName=>$aRoute)
			{
				$aRoute['file'] = $oFile->getPathname();
				$aRoute['basename'] = $sName;
				$aRoute['basepath'] = $aRoute['path'];

				$this->aRoutesFromFiles[$sName] = $aRoute;
			}
		}

		return $this->aRoutesFromFiles;
	}

	/**
	 * Return yaml routes files.
	 *
	 * @return Ambigous Symfony\Component\Finder\Finder|NULL
	 */
	public function getFiles()
	{
		if ($this->oFiles !== null) {
			return $this->oFiles;
		}

		if (is_dir($this->sPath))
		{
			$this->oFiles = Finder::create();
			$this->oFiles
				->files()
				->in($this->sPath)
				->name('*.yml');

			return $this->oFiles;
		}

		return null;
	}

	public function getEmptyRoute()
	{
		return array(
			'path' => '',
			'defaults' => array(),
			'requirements' => array(),
			'options' => array(),
			'host' => '',
			'schemes' => array(),
			'methods' => array(),
			'condition' => null
		);
	}
}
