<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Extensions\Manage\Component;

use Okatea\Tao\Extensions\Manage\Component\ComponentBase;

class AssetsFiles extends ComponentBase
{
	protected $sDestination;

	public function __construct($okt, $extension, $sDestinationPattern)
	{
		parent::__construct($okt, $extension);

		$this->sDestination = sprintf($sDestinationPattern, $this->extension->id());
	}

	/**
	 * Copy/replace assets files.
	 *
	 * @return void
	 */
	public function process()
	{
		$sAssetsDir = $this->extension->root().'/Install/assets';

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
				$this->sDestination,
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
		if (!is_dir($this->sDestination)) {
			return null;
		}

		$this->checklist->addItem(
			'remove_assets',
			$this->getFs()->remove($this->sDestination),
			'Remove assets files',
			'Cannot remove assets files'
		);
	}

	protected function getFiles()
	{
		$sPath = $this->extension->root().'/Install/assets';

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
