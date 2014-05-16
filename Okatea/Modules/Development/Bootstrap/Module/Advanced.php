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
	 */
	protected function makeDirs()
	{
		if (file_exists($this->dir))
		{
			throw new \Exception(sprintf(__('m_development_bootstrap_module_allready_exists'), $this->id));
		}
		
		\files::makeDir($this->dir);
		\files::makeDir($this->dir . '/Install', true);
		\files::makeDir($this->dir . '/Install/Assets', true);
		\files::makeDir($this->dir . '/Install/public', true);
		\files::makeDir($this->dir . '/Install/Templates', true);
		//		\files::makeDir($this->dir.'/Install/TestSet',true);
		

		\files::makeDir($this->dir . '/inc', true);
		\files::makeDir($this->dir . '/inc/admin', true);
		\files::makeDir($this->dir . '/inc/public', true);
		
		\files::makeDir($this->dir . '/Locales', true);
		\files::makeDir($this->dir . '/Locales/fr', true);
		\files::makeDir($this->dir . '/Locales/en', true);
	}

	/**
	 * Make files
	 */
	protected function makeFiles()
	{
		$replacements = $this->getReplacements();
		
		$this->makeFile('db-install', $this->dir . '/Install/db-install.xml', $replacements);
		$this->makeFile('db-truncate', $this->dir . '/Install/db-truncate.xml', $replacements);
		$this->makeFile('db-uninstall', $this->dir . '/Install/db-uninstall.xml', $replacements);
		$this->makeFile('config', $this->dir . '/Install/conf_' . $this->id . '.yml', $replacements);
		
		copy($this->getTplPath('preview_icon'), $this->dir . '/Install/Assets/preview.png');
		$this->makeFile('common_css', $this->dir . '/Install/Assets/styles.css', $replacements);
		
		$this->makeFile('public_list', $this->dir . '/Install/public/oktPublic_' . $this->id . '_list.php', $replacements);
		$this->makeFile('public_item', $this->dir . '/Install/public/oktPublic_' . $this->id . '_item.php', $replacements);
		
		$this->makeFile('tpl_list', $this->dir . '/Install/tpl/' . $this->id . '_list_tpl.php', $replacements);
		$this->makeFile('tpl_item', $this->dir . '/Install/tpl/' . $this->id . '_item_tpl.php', $replacements);
		
		$this->makeFile('admin_index', $this->dir . '/Admin/index.php', $replacements);
		$this->makeFile('admin_display', $this->dir . '/Admin/display.php', $replacements);
		$this->makeFile('admin_config', $this->dir . '/Admin/config.php', $replacements);
		$this->makeFile('admin_item', $this->dir . '/Admin/item.php', $replacements);
		
		$this->makeFile('public_prepend_list', $this->dir . '/inc/public/list.php', $replacements);
		$this->makeFile('public_prepend_item', $this->dir . '/inc/public/item.php', $replacements);
		
		$this->makeFile('filters', $this->dir . '/inc/class.' . $this->id . '.filters.php', $replacements);
		$this->makeFile('recordset', $this->dir . '/inc/class.' . $this->id . '.recordset.php', $replacements);
		
		$this->makeFile('locales_main_en', $this->dir . '/Locales/en/main.lang.php', $replacements);
		$this->makeFile('locales_main_fr', $this->dir . '/Locales/fr/main.lang.php', $replacements);
		$this->makeFile('locales_admin_en', $this->dir . '/Locales/en/admin.lang.php', $replacements);
		$this->makeFile('locales_admin_fr', $this->dir . '/Locales/fr/admin.lang.php', $replacements);
		
		$this->makeFile('define', $this->dir . '/_define.php', $replacements);
		$this->makeFile('admin', $this->dir . '/admin.php', $replacements);
		$this->makeFile('changelog', $this->dir . '/CHANGELOG', $replacements);
		$this->makeFile('index', $this->dir . '/index.php', $replacements);
		$this->makeFile('module', $this->dir . '/module.php', $replacements);
	}
}

