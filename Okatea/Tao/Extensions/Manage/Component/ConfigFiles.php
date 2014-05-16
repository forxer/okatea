<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Extensions\Manage\Component;

use Okatea\Tao\Extensions\Manage\Component\ComponentBase;

class ConfigFiles extends ComponentBase
{

	/**
	 * Copy/merge config files.
	 *
	 * @return void
	 */
	public function process()
	{
		$oFiles = $this->getFiles();
		
		if (empty($oFiles))
		{
			return null;
		}
		
		foreach ($oFiles as $oFile)
		{
			$sConfigFile = $this->okt->options->get('config_dir') . '/' . $oFile->getFilename();
			
			if (file_exists($sConfigFile))
			{
				$this->merge($oFile, $sConfigFile);
			}
			else
			{
				$this->copy($oFile, $sConfigFile);
			}
		}
	}

	/**
	 * Delete config files.
	 */
	public function delete()
	{
		$oFiles = $this->getFiles();
		
		if (empty($oFiles))
		{
			return null;
		}
		
		foreach ($oFiles as $oFile)
		{
			$sBasename = $oFile->getBasename($oFile->getExtension());
			
			# si le fichier cache existe on le supprime
			if (file_exists($this->okt->options->get('cache_dir') . '/' . $oFile->getFilename() . '.php'))
			{
				$this->checklist->addItem('cached_config_file_' . $sBasename, unlink($this->okt->options->get('cache_dir') . '/' . $oFile->getFilename() . '.php'), 'Remove cached config file ' . $oFile->getFilename() . '.php', 'Cannot remove cached config file ' . $oFile->getFilename() . '.php');
			}
			else
			{
				$this->checklist->addItem('config_file_' . $sBasename, null, 'Cached config file ' . $oFile->getFilename() . ' doesn\'t exists', 'Cached config file ' . $oFile->getFilename() . ' doesn\'t exists');
			}
			
			# si le fichier config existe on le supprime
			if (file_exists($this->okt->options->get('config_dir') . '/' . $oFile->getFilename()))
			{
				$this->checklist->addItem('config_file_' . $sBasename, unlink($this->okt->options->get('config_dir') . '/' . $oFile->getFilename()), 'Remove config file ' . $oFile->getFilename(), 'Cannot remove config file ' . $oFile->getFilename());
			}
			else
			{
				$this->checklist->addItem('config_file_' . $sBasename, null, 'Config file ' . $oFile->getFilename() . ' doesn\'t exists', 'Config file ' . $oFile->getFilename() . ' doesn\'t exists');
			}
		}
	}

	/**
	 * Copy a config file.
	 */
	protected function copy($sNewFile, $sConfigFile)
	{
		$this->checklist->addItem('config_file_' . $sNewFile->getBasename($sNewFile->getExtension()), $this->getFs()
			->copy($sNewFile->getRealPath(), $sConfigFile), 'Copy config file ' . $sNewFile->getFilename(), 'Cannot copy config file ' . $sNewFile->getFilename());
	}

	/**
	 * Merge config files.
	 */
	protected function merge($sNewFile, $sConfigFile)
	{
		$this->checklist->addItem('merging_config_file_' . $sNewFile->getBasename($sNewFile->getExtension()), $this->doMerging($sNewFile->getRealPath(), $sConfigFile), 'Merging config file ' . $sNewFile->getFilename(), 'Cannot merging config file ' . $sNewFile->getFilename());
	}

	protected function doMerging($sNewFile, $sConfigFile)
	{
		$aConfig = $this->yamlParse($sConfigFile);
		$aNewConfig = $this->yamlParse($sNewFile);
		
		$aData = $aConfig + $aNewConfig;
		
		return file_put_contents($sConfigFile, $this->yamlDump($aData));
	}

	protected function getFiles()
	{
		$sPath = $this->extension->root() . '/Install';
		
		if (is_dir($sPath))
		{
			$finder = $this->getFinder();
			$finder->files()
				->in($sPath)
				->depth('== 0')
				->name('conf_*.yml');
			
			return $finder;
		}
		
		return null;
	}
}
