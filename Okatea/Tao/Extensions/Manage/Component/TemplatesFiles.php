<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Extensions\Manage\Component;

use Okatea\Tao\Extensions\Manage\Component\ComponentBase;
use Okatea\Tao\Extensions\Themes\Collection as ThemesCollection;

class TemplatesFiles extends ComponentBase
{
	/**
	 * Copy/replace templates files.
	 *
	 * @return void
	 */
	public function process()
	{
		$sTemplatesDir = $this->extension->root().'/Install/templates';

		if (!is_dir($sTemplatesDir)) {
			return null;
		}

		$oFiles = $this->getFiles();

		if (empty($oFiles)) {
			return null;
		}

		$this->checklist->addItem(
			'templates_files',
			$this->mirror(
				$sTemplatesDir,
				$this->okt->options->get('themes_dir').'/'.ThemesCollection::DEFAULT_THEME.'/templates/'.$this->extension->id(),
				$oFiles
			),
			'Create templates files',
			'Cannot create templates files'
		);
	}

	/**
	 * Delete templates directory.
	 *
	 */
	public function delete()
	{
		$sPath = $this->okt->options->get('themes_dir').'/'.ThemesCollection::DEFAULT_THEME.'/templates/'.$this->extension->id();

		if (!is_dir($sPath)) {
			return null;
		}

		$this->checklist->addItem(
			'remove_templates',
			$this->getFs()->remove($sPath),
			'Remove templates files',
			'Cannot remove templates files'
		);
	}

	protected function getFiles()
	{
		$sPath = $this->extension->root().'/Install/templates';

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
