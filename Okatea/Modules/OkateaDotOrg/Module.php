<?php

/**
 * @ingroup okt_module_okatea_dot_org
 * @brief La classe principale du Module Okatea.org.
 *
 */
namespace Okatea\Modules\OkateaDotOrg;

use Okatea\Tao\Extensions\Modules\Module as BaseModule;
use Symfony\Component\Filesystem\Filesystem;

class Module extends BaseModule
{

	protected $sUrl;

	protected function prepend()
	{
		$this->sRepositoryPath = realpath(__DIR__ . '/../../../repository/');
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

	public function getLatestStableVersionInfos()
	{
		return $this->getVersionInfo('stable');
	}

	public function getLatestDevVersionInfos()
	{
		return $this->getVersionInfo('dev');
	}

	protected function getVersionInfo($sVersionType)
	{
		static $aVersionInfo = null;
		
		if (null === $aVersionInfo)
		{
			$sFilename = $this->sRepositoryPath . '/packages/index.json';
			
			if (! file_exists($sFilename))
			{
				$this->okt->error->set('json file of repository not found.');
			}
			
			$aVersionInfo = json_decode(file_get_contents($sFilename));
		}
		
		$this->resetVersionInfos();
		
		$sVersionType = ($sVersionType == 'dev' ? 'dev' : 'stable');
		
		return $aVersionInfo[$sVersionType];
	}
}
