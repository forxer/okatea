<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Extensions\Themes\Manage;

use Okatea\Tao\Extensions\Manage\Installer as BaseInstaller;
use Okatea\Tao\Extensions\Manage\Component\AssetsFiles;
use Okatea\Tao\Themes\Collection as ThemesCollection;

class Installer extends BaseInstaller
{
	/**
	 * Return manager instance.
	 *
	 * @return \Okatea\Tao\Extensions\Manager
	 */
	protected function getManager()
	{
		return $this->okt->themes->getManager();
	}

	public function compareFiles()
	{
		$this->getComparator()->folder($this->root().'/Install/assets/', $this->okt->options->get('public_dir').'/themes/'.$this->id().'/');
	}

	protected function getAssetsFiles()
	{
		if (null === $this->assetsFiles) {
			$this->assetsFiles = new AssetsFiles($this->okt, $this, $this->okt->options->get('public_dir').'/themes/%s');
		}

		return $this->assetsFiles;
	}
}
