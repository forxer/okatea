<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Routing;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class ConfigHelpers
{
	protected $okt;
	protected $sPath;

	protected $oFiles;
	protected $aLoadedRoutes;
	protected $aRoutesFromFiles;

	public function __construct($okt, $sPath)
	{
		$this->okt = $okt;
		$this->sPath = $sPath;
	}

	public function getRoutesInfos()
	{
		$aLoadedRoutes = $this->getLoadedRoutes();
		$aRoutesFromFiles = $this->getRoutesFromFiles();

		$aRoutesInfos = array();
		foreach ($aRoutesFromFiles as $sName=>$aRoute)
		{
			$aRoutesInfos[$sName] = array_merge($this->getEmptyRoute(), $aRoutesFromFiles[$sName]);

			$aRoutesInfos[$sName]['loaded'] = array_key_exists(($this->okt->languages->unique ? $aRoutesInfos[$sName]['basename'] : $sName), $aLoadedRoutes);

			if ($this->okt->languages->unique && $aRoutesInfos[$sName]['language'] != $this->okt->config->language) {
				$aRoutesInfos[$sName]['loaded'] = false;
			}
		}

		# put loaded routes at top
		uasort($aRoutesInfos, function($a, $b){
			if ($a['loaded'] == $b['loaded']) {
				return 0;
			}
			return  $b['loaded'] - $a['loaded'];
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

		foreach ($this->okt->router->getRouteCollection()->all() as $sName=>$oRoute)
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

			$sLanguage = basename(dirname($oFile->getPathname()));

			foreach ($aRoutes as $sName=>$aRoute)
			{
				$aRoute['file'] = $oFile->getPathname();
				$aRoute['basename'] = $sName;
				$aRoute['path'] = '/'.$sLanguage.$aRoute['path'];
				$aRoute['language'] = $sLanguage;

				$sName .= '-'.$sLanguage;

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
