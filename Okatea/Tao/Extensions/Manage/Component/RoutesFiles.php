<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Extensions\Manage\Component;

use Okatea\Tao\Extensions\Manage\Component\ComponentBase;

class RoutesFiles extends ComponentBase
{

	protected $sRoutesDirectory;

	public function setRoutesDirectory($sRoutesDirectory)
	{
		$this->sRoutesDirectory = $sRoutesDirectory;
	}

	public function getRoutesDirectory()
	{
		return $this->sRoutesDirectory ? $this->sRoutesDirectory : 'routes';
	}

	/**
	 * Copy/merge routes files.
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
			$sRouteFile = $this->okt->options->get('config_dir') . '/' . $this->getRoutesDirectory() . '/' . $oFile->getRelativePathname();
			
			if (file_exists($sRouteFile))
			{
				$this->merge($oFile, $sRouteFile);
			}
			else
			{
				$this->copy($oFile, $sRouteFile);
			}
		}
	}

	/**
	 * Delete routes files.
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
			$sFilePath = $this->okt->options->get('config_dir') . '/' . $this->getRoutesDirectory() . '/' . $oFile->getRelativePathname();
			
			if (file_exists($sFilePath))
			{
				$this->checklist->addItem($this->getRoutesDirectory() . '_file_' . $oFile->getRelativePath() . '_' . $oFile->getBasename($oFile->getExtension()), unlink($sFilePath), 'Remove routes file ' . $oFile->getRelativePathname(), 'Cannot remove routes file ' . $oFile->getRelativePathname());
			}
			else
			{
				$this->checklist->addItem($this->getRoutesDirectory() . '_file_' . $oFile->getRelativePath() . '_' . $oFile->getBasename($oFile->getExtension()), null, 'Routes file ' . $oFile->getRelativePathname() . ' doesn\'t exists', 'Routes file ' . $oFile->getRelativePathname() . ' doesn\'t exists');
			}
		}
	}

	/**
	 * Copy a routes file.
	 */
	protected function copy($sNewFile, $sRouteFile)
	{
		$this->checklist->addItem($this->getRoutesDirectory() . '_file_' . $sNewFile->getRelativePath() . '_' . $sNewFile->getBasename($sNewFile->getExtension()), $this->getFs()
			->copy($sNewFile->getRealPath(), $sRouteFile), 'Copy routes file ' . $sNewFile->getRelativePathname(), 'Cannot copy routes file ' . $sNewFile->getRelativePathname());
	}

	/**
	 * Merge routes files.
	 */
	protected function merge($sNewFile, $sRouteFile)
	{
		$this->checklist->addItem($this->getRoutesDirectory() . '_merging_file_' . $sNewFile->getRelativePath() . '_' . $sNewFile->getBasename($sNewFile->getExtension()), $this->doMerging($sNewFile->getRealPath(), $sRouteFile), 'Merging routes file ' . $sNewFile->getRelativePathname(), 'Cannot merging routes file ' . $sNewFile->getRelativePathname());
	}

	protected function doMerging($sNewFile, $sRouteFile)
	{
		$aRoutes = $this->yamlParse($sRouteFile);
		$aNewRoutes = $this->yamlParse($sNewFile);
		
		$aData = $aRoutes + $aNewRoutes;
		
		return file_put_contents($sRouteFile, $this->yamlDump($aData));
	}

	protected function getFiles()
	{
		$sPath = $this->extension->root() . '/Install/' . $this->getRoutesDirectory();
		
		if (is_dir($sPath))
		{
			$finder = $this->getFinder();
			$finder->files()
				->in($sPath)
				->name('*.yml');
			
			return $finder;
		}
		
		return null;
	}
}
