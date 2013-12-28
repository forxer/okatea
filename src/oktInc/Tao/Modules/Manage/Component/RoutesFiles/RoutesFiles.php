<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Modules\Manage\Component\RoutesFiles;

use Tao\Modules\Manage\Component\ComponentBase;

class RoutesFiles extends ComponentBase
{
	/**
	 * Copy/merge routes files.
	 *
	 * @return void
	 */
	public function process()
	{
		$oFiles = $this->getFiles();

		if (empty($oFiles)) {
			return null;
		}

		foreach ($oFiles as $oFile)
		{
			$sRouteFile = $this->okt->options->get('config_dir').'/routes/'.$oFile->getRelativePathname();

			if (file_exists($sRouteFile)) {
				$this->merge($oFile, $sRouteFile);
			}
			else {
				$this->copy($oFile, $sRouteFile);
			}
		}
	}

	/**
	 * Delete routes files.
	 *
	 */
	public function delete()
	{
		$oFiles = $this->getFiles();

		if (empty($oFiles)) {
			return null;
		}

		foreach ($oFiles as $oFile)
		{
			$sFilePath = $this->okt->options->get('config_dir').'/routes/'.$oFile->getRelativePathname();

			if (file_exists($sFilePath))
			{
				$this->checklist->addItem(
					'routes_file_'.$oFile->getRelativePath().'_'.$oFile->getBasename($oFile->getExtension()),
					unlink($sFilePath),
					'Remove routes file '.$oFile->getRelativePathname(),
					'Cannot remove routes file '.$oFile->getRelativePathname()
				);
			}
			else {
				$this->checklist->addItem(
					'routes_file_'.$oFile->getRelativePath().'_'.$oFile->getBasename($oFile->getExtension()),
					null,
					'Routes file '.$oFile->getRelativePathname().' doesn\'t exists',
					'Routes file '.$oFile->getRelativePathname().' doesn\'t exists'
				);
			}
		}
	}

	/**
	 * Copy a routes file.
	 *
	 */
	protected function copy($sNewFile, $sRouteFile)
	{
		$this->checklist->addItem(
			'routes_file_'.$sNewFile->getRelativePath().'_'.$sNewFile->getBasename($sNewFile->getExtension()),
			$this->getFs()->copy($sNewFile->getRealPath(), $sRouteFile),
			'Copy routes file '.$sNewFile->getRelativePathname(),
			'Cannot copy routes file '.$sNewFile->getRelativePathname()
		);
	}

	/**
	 * Merge routes files.
	 *
	 */
	protected function merge($sNewFile, $sRouteFile)
	{
		$this->checklist->addItem(
			'merging_routes_file_'.$sNewFile->getRelativePath().'_'.$sNewFile->getBasename($sNewFile->getExtension()),
			$this->doMerging($sNewFile->getRealPath(), $sRouteFile),
			'Merging routes file '.$sNewFile->getRelativePathname(),
			'Cannot merging routes file '.$sNewFile->getRelativePathname()
		);
	}

	protected function doMerging($sNewFile, $sRouteFile)
	{
		try {
			$aRoutes = $this->yamlParse($sRouteFile);
			$aNewRoutes = $this->yamlParse($sNewFile);

			$aData = $aRoutes + $aNewRoutes;
		}
		catch (Exception $e) {
			return false;
		}

		return file_put_contents($sRouteFile, $this->yamlDump($aData));
	}

	protected function getFiles()
	{
		$sPath = $this->module->root().'/install/routes';

		if (is_dir($sPath))
		{
			$finder = $this->getFinder();
			$finder
				->files()
				->in($sPath)
				->name('*.yml');

			return $finder;
		}

		return null;
	}
}
