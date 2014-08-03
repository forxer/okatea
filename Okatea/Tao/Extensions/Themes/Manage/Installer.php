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
		return $this->okt['themes']->getManager();
	}

	/**
	 * Test prerequisites to install a theme
	 *
	 * @return boolean
	 */
	protected function preInstall()
	{
		# présence du fichier /Theme.php
		$this->checklist->addItem('theme_file', file_exists($this->root() . '/Theme.php'), 'Theme handler file exists', 'Theme handler file doesn\'t exists');
		
		# existence de la class Okatea\Themes\<id_module>\Theme
		if ($this->checklist->checkItem('theme_file'))
		{
			include $this->root() . '/Theme.php';
			
			$sClassName = 'Okatea\\Themes\\' . $this->id() . '\\Theme';
			
			$this->checklist->addItem('theme_class', class_exists($sClassName), 'Theme handler class "' . $sClassName . '" exists', 'Theme handler class "' . $sClassName . '" doesn\'t exists');
			
			$this->checklist->addItem('theme_class_valid', is_subclass_of($sClassName, '\\Okatea\\Tao\\Extensions\\Themes\\Theme'), 'Theme handler class "' . $sClassName . '" is a valid theme class', 'Theme handler class "' . $sClassName . '" is not a valid theme class');
		}
		
		return $this->checklist->checkItem('theme_file') && $this->checklist->checkItem('theme_class') && $this->checklist->checkItem('theme_class_valid');
	}

	public function compareFiles()
	{
		$this->getComparator()->folder($this->root() . '/Install/Assets/', $this->okt['public_dir'] . '/themes/' . $this->id() . '/');
	}

	protected function getAssetsFiles()
	{
		if (null === $this->assetsFiles)
		{
			$this->assetsFiles = new AssetsFiles($this->okt, $this, $this->okt['public_dir'] . '/themes/%s');
		}
		
		return $this->assetsFiles;
	}
}
