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
	 * Constructor.
	 *
	 * @param object $okt Okatea application instance.
	 * @param string $sPath The extensions directory path to load.
	 * @param string $sExtensionId
	 * @return void
	 */
	public function __construct($okt, $sPath, $sExtensionId)
	{
		parent::__construct($okt, $sPath, $sExtensionId);

		$this->sManagerClass = '\\Okatea\Tao\\Extensions\\Themes\\Manager';
	}

	/*
	public function compareFiles()
	{
		# compare templates
		$this->getComparator()->folder($this->root().'/Install/tpl/', $this->okt->options->get('themes_dir').'/default/templates/');

		foreach (ThemesCollection::getThemes() as $sThemeId=>$sTheme)
		{
			if ($sThemeId == 'default') {
				continue;
			}

			$this->getComparator()->folder($this->root().'/Install/tpl/', $this->okt->options->get('themes_dir').'/'.$sThemeId.'/templates/', true);
		}

		# compare assets
		$this->getComparator()->folder($this->root().'/Install/assets/', $this->okt->options->get('public_dir').'/modules/'.$this->id().'/');
	}
	*/

	protected function getAssetsFiles()
	{
		if (null === $this->assetsFiles) {
			$this->assetsFiles = new AssetsFiles($this->okt, $this, $this->okt->options->get('public_dir').'/themes/%s');
		}

		return $this->assetsFiles;
	}
}
