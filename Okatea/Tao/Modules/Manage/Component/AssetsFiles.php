<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Modules\Manage\Component;

use Okatea\Tao\Modules\Manage\Component\ComponentBase;

class AssetsFiles extends ComponentBase
{
	/**
	 * Copy/replace assets files.
	 *
	 * @return void
	 */
	public function process()
	{
		$sAssetsDir = $this->module->root().'/Install/assets';

		if (!is_dir($sAssetsDir)) {
			return null;
		}

		$oFiles = $this->getFiles();

		if (empty($oFiles)) {
			return null;
		}

		$this->checklist->addItem(
			'assets',
			$this->mirror(
				$sAssetsDir,
				$this->okt->options->get('public_dir').'/modules/'.$this->module->id(),
				$oFiles
			),
			'Create assets files',
			'Cannot create assets files'
		);
	}

	/**
	 * Delete assets directory.
	 *
	 */
	public function delete()
	{
		$sPath = $this->okt->options->get('public_dir').'/modules/'.$this->module->id();

		if (!is_dir($sPath)) {
			return null;
		}

		$this->checklist->addItem(
			'remove_assets',
			$this->getFs()->remove($sPath),
			'Remove assets files',
			'Cannot remove assets files'
		);
	}

	protected function getFiles()
	{
		$sPath = $this->module->root().'/Install/assets';

		if (is_dir($sPath))
		{
			$finder = $this->getFinder();
			$finder->in($sPath);

			return $finder;
		}

		return null;
	}

	protected function mirror($src, $dest, $oFiles)
	{
		return $this->getFs()->mirror($src, $dest, $oFiles, array(
			'override' 			=> true,
			'copy_on_windows' 	=> true,
			'delete' 			=> false
		));
	}
}
