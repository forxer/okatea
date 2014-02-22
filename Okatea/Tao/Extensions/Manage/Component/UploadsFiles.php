<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Extensions\Manage\Component;

use Okatea\Tao\Extensions\Manage\Component\ComponentBase;

class UploadsFiles extends ComponentBase
{
	/**
	 * Copy/replace uploads files.
	 *
	 * @return void
	 */
	public function process()
	{
		$sUploadsDir = $this->extension->root().'/Install/test_set/upload';

		if (!is_dir($sUploadsDir)) {
			return null;
		}

		$oFiles = $this->getFiles();

		if (empty($oFiles)) {
			return null;
		}

		$this->checklist->addItem(
			'upload_files',
			$this->mirror(
				$sUploadsDir,
				$this->okt->options->get('upload_dir').'/'.$this->extension->id(),
				$oFiles
			),
			'Create upload files',
			'Cannot create upload files'
		);
	}

	/**
	 * Delete upload directory.
	 *
	 */
	public function delete()
	{
		$sPath = $this->okt->options->get('upload_dir').'/'.$this->extension->id();

		if (!is_dir($sPath)) {
			return null;
		}

		$this->checklist->addItem(
			'remove_assets',
			$this->getFs()->remove($sPath),
			'Remove upload files',
			'Cannot remove upload files'
		);
	}

	protected function getFiles()
	{
		$sPath = $this->extension->root().'/Install/test_set/upload';

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
