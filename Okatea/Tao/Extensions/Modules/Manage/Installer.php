<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Extensions\Modules\Manage;

use Okatea\Tao\Extensions\Manage\Installer as BaseInstaller;
use Okatea\Tao\Extensions\Manage\Component\AssetsFiles;
use Okatea\Tao\Themes\Collection as ThemesCollection;

class Installer extends BaseInstaller
{
	/**
	 * Reserved modules ID
	 * @var array
	 */
	private static $aReservedIds = array(
		'autoloader', 			'debug', 				'debugBar',
		'cacheConfig', 			'config', 				'db',
		'error', 				'languages', 			'l10n',
		'logAdmin', 			'modules', 				'navigation',
		'page', 				'request', 				'requestContext',
		'response', 			'router', 				'adminRouter',
		'session', 				'theme', 				'theme_id',
		'tpl', 					'triggers', 			'user',
		'htmlpurifier', 		'permsStack', 			'aTplDirectories'
	);

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

		$this->sManagerClass = '\\Okatea\Tao\\Extensions\\Modules\\Manager';
	}

	/**
	 * Test prerequisites to install an extension.
	 *
	 * @return boolean
	 */
	protected function preInstall()
	{
		# identifiant non-réservé ?
		$this->checklist->addItem(
			'module_id_not_reserved',
			!in_array($this->id(), self::$aReservedIds),
			'Module id not reserved',
			'Module id can not be one of: "'.implode('", "', self::$aReservedIds).'"'
		);

		# présence du fichier /Module.php
		$this->checklist->addItem(
			'module_file',
			file_exists($this->root().'/Module.php'),
			'Module handler file exists',
			'Module handler file doesn\'t exists'
		);

		# existence de la class module_<id_module>
		if ($this->checklist->checkItem('module_file'))
		{
			include $this->root().'/Module.php';

			$sClassName = 'Okatea\\Modules\\'.$this->id().'\\Module';

			$this->checklist->addItem(
				'module_class',
				class_exists($sClassName),
				'Module handler class "'.$sClassName.'" exists',
				'Module handler class "'.$sClassName.'" doesn\'t exists'
			);

			$this->checklist->addItem(
				'module_class_valide',
				is_subclass_of($sClassName, '\\Okatea\\Tao\\Modules\\Module'),
				'Module handler class "'.$sClassName.'" is a valid module class',
				'Module handler class "'.$sClassName.'" is not a valid module class'
			);
		}

		return $this->checklist->checkItem('module_file')
			&& $this->checklist->checkItem('module_class')
			&& $this->checklist->checkItem('module_class_valide');
	}

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

	protected function getAssetsFiles()
	{
		if (null === $this->assetsFiles) {
			$this->assetsFiles = new AssetsFiles($this->okt, $this, $this->okt->options->get('public_dir').'/modules/%s');
		}

		return $this->assetsFiles;
	}
}
