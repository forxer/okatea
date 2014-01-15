<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Modules\Manage\Component\AssetsFiles;

use Tao\Modules\Manage\Component\ComponentBase;

class AssetsFiles extends ComponentBase
{
	/**
	 * Copy/replace assets files.
	 *
	 * @return void
	 */
	public function process()
	{
		$oFiles = $this->getFiles();

		if (empty($oFiles)) {
			return null;
		}

		return $this->getFs()->mirror(
			$this->module->root().'/install/assets',
			$this->okt->options->get('public_dir').'/modules/'.$this->module->id(),
			$oFiles,
			array(
				'override' 			=> true,
				'copy_on_windows' 	=> true,
				'delete' 			=> false
			)
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
		$sPath = $this->module->root().'/install/assets';

		if (is_dir($sPath))
		{
			$finder = $this->getFinder();
			$finder->in($sPath);

			return $finder;
		}

		return null;
	}
}
