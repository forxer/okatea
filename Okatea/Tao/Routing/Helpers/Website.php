<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Routing\Helpers;

use Symfony\Component\Yaml\Yaml;

class Website extends Config
{

	public function getRoutesInfos()
	{
		$aLoadedRoutes = $this->getLoadedRoutes();
		$aRoutesFromFiles = $this->getRoutesFromFiles();
		
		$aRoutesInfos = array();
		foreach ($aRoutesFromFiles as $sName => $aRoute)
		{
			$aRoutesInfos[$sName] = array_merge($this->getEmptyRoute(), $aRoutesFromFiles[$sName]);
			
			$aRoutesInfos[$sName]['loaded'] = array_key_exists(($this->okt->languages->unique ? $aRoutesInfos[$sName]['basename'] : $sName), $aLoadedRoutes);
			
			if ($this->okt->languages->unique && $aRoutesInfos[$sName]['language'] != $this->okt['config']->language)
			{
				$aRoutesInfos[$sName]['loaded'] = false;
			}
			
			$aRoutesInfos[$sName]['controller'] = $aRoutesInfos[$sName]['defaults']['controller'];
			unset($aRoutesInfos[$sName]['defaults']['controller']);
		}
		
		uasort($aRoutesInfos, function ($a, $b)
		{
			# put loaded routes at top
			if ($a['loaded'] !== $b['loaded'])
			{
				return $b['loaded'] - $a['loaded'];
			}
			
			# group by filename
			$c = strcasecmp($a['file'], $b['file']);
			
			if ($c !== 0)
			{
				return $c;
			}
			
			return 0;
		});
		
		return $aRoutesInfos;
	}

	/**
	 * Return routes from yaml files.
	 *
	 * @return array
	 */
	public function getRoutesFromFiles()
	{
		if ($this->aRoutesFromFiles !== null)
		{
			return $this->aRoutesFromFiles;
		}
		
		$oRoutesFiles = $this->getFiles();
		
		if (empty($oRoutesFiles))
		{
			return null;
		}
		
		$this->aRoutesFromFiles = array();
		
		foreach ($oRoutesFiles as $oFile)
		{
			$aRoutes = Yaml::parse(file_get_contents($oFile->getPathname()));
			
			$sLanguage = basename(dirname($oFile->getPathname()));
			
			foreach ($aRoutes as $sName => $aRoute)
			{
				$aRoute['file'] = $oFile->getPathname();
				$aRoute['basename'] = $sName;
				$aRoute['basepath'] = $aRoute['path'];
				$aRoute['language'] = $sLanguage;
				
				$aRoute['path'] = '/' . $sLanguage . $aRoute['path'];
				$sName .= '-' . $sLanguage;
				
				$this->aRoutesFromFiles[$sName] = $aRoute;
			}
		}
		
		return $this->aRoutesFromFiles;
	}
}
