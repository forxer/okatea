<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Development\Bootstrap\Module;

class Advanced extends Module
{

	/**
	 * Make basis directories
	 *
	 */
	protected function makeDirs()
	{
		if (file_exists($this->dir)) {
			throw new \Exception(sprintf(__('m_development_bootstrap_module_allready_exists'),$this->id));
		}

		\files::makeDir($this->dir);
		\files::makeDir($this->dir.'/install',true);
		\files::makeDir($this->dir.'/install/assets',true);
		\files::makeDir($this->dir.'/install/public',true);
		\files::makeDir($this->dir.'/install/tpl',true);
//		\files::makeDir($this->dir.'/install/test_set',true);

		\files::makeDir($this->dir.'/inc',true);
		\files::makeDir($this->dir.'/inc/admin',true);
		\files::makeDir($this->dir.'/inc/public',true);

		\files::makeDir($this->dir.'/locales',true);
		\files::makeDir($this->dir.'/locales/fr',true);
		\files::makeDir($this->dir.'/locales/en',true);
	}

	/**
	 * Make files
	 *
	 */
	protected function makeFiles()
	{
		$replacements = $this->getReplacements();

		$this->makeFile('db-install', 		$this->dir.'/install/db-install.xml', $replacements);
		$this->makeFile('db-truncate', 		$this->dir.'/install/db-truncate.xml', $replacements);
		$this->makeFile('db-uninstall', 	$this->dir.'/install/db-uninstall.xml', $replacements);
		$this->makeFile('config', 			$this->dir.'/install/conf_'.$this->id.'.yml', $replacements);

		copy($this->getTplPath('preview_icon'), $this->dir.'/install/assets/preview.png');
		$this->makeFile('common_css', 		$this->dir.'/install/assets/styles.css', $replacements);

		$this->makeFile('public_list', 		$this->dir.'/install/public/oktPublic_'.$this->id.'_list.php', $replacements);
		$this->makeFile('public_item', 		$this->dir.'/install/public/oktPublic_'.$this->id.'_item.php', $replacements);

		$this->makeFile('tpl_list', 		$this->dir.'/install/tpl/'.$this->id.'_list_tpl.php', $replacements);
		$this->makeFile('tpl_item', 		$this->dir.'/install/tpl/'.$this->id.'_item_tpl.php', $replacements);

		$this->makeFile('admin_index', 		$this->dir.'/admin/index.php', $replacements);
		$this->makeFile('admin_display', 	$this->dir.'/admin/display.php', $replacements);
		$this->makeFile('admin_config', 	$this->dir.'/admin/config.php', $replacements);
		$this->makeFile('admin_item', 		$this->dir.'/admin/item.php', $replacements);

		$this->makeFile('public_prepend_list', 		$this->dir.'/inc/public/list.php', $replacements);
		$this->makeFile('public_prepend_item', 		$this->dir.'/inc/public/item.php', $replacements);

		$this->makeFile('filters', 			$this->dir.'/inc/class.'.$this->id.'.filters.php', $replacements);
		$this->makeFile('recordset', 		$this->dir.'/inc/class.'.$this->id.'.recordset.php', $replacements);

		$this->makeFile('locales_main_en', 	$this->dir.'/locales/en/main.lang.php', $replacements);
		$this->makeFile('locales_main_fr', 	$this->dir.'/locales/fr/main.lang.php', $replacements);
		$this->makeFile('locales_admin_en', $this->dir.'/locales/en/admin.lang.php', $replacements);
		$this->makeFile('locales_admin_fr', $this->dir.'/locales/fr/admin.lang.php', $replacements);

		$this->makeFile('define', 			$this->dir.'/_define.php', $replacements);
		$this->makeFile('admin', 			$this->dir.'/admin.php', $replacements);
		$this->makeFile('changelog', 		$this->dir.'/CHANGELOG', $replacements);
		$this->makeFile('index', 			$this->dir.'/index.php', $replacements);
		$this->makeFile('module', 			$this->dir.'/module.php', $replacements);
	}

}

