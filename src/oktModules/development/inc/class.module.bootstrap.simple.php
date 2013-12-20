<?php
/**
 * @class oktModuleInstall
 * @ingroup okt_module_development
 * @brief Amorçage d'un module
 *
 */

class oktModuleBootstrapSimple extends oktModuleBootstrap
{

	/**
	 * Make basis directories
	 *
	 */
	protected function makeDirs()
	{
		if (file_exists($this->dir)) {
			throw new Exception(sprintf(__('m_development_bootstrap_module_allready_exists'),$this->id));
		}

		files::makeDir($this->dir);

		files::makeDir($this->dir.'/install',true);
			files::makeDir($this->dir.'/install/tpl',true);

		files::makeDir($this->dir.'/inc',true);
			files::makeDir($this->dir.'/inc/admin',true);

		files::makeDir($this->dir.'/locales',true);
			files::makeDir($this->dir.'/locales/fr',true);
			files::makeDir($this->dir.'/locales/en',true);
	}

	/**
	 * Make files
	 *
	 */
	protected function makeFiles()
	{
		$replacements = $this->getReplacements();

		$this->makeFile('config', 			$this->dir.'/install/conf_'.$this->id.'.yaml', $replacements);

		$this->makeFile('tpl_base', 		$this->dir.'/install/tpl/'.$this->id.'_tpl.php', $replacements);

		$this->makeFile('admin_config', 	$this->dir.'/admin/config.php', $replacements);

		$this->makeFile('controller', 		$this->dir.'/inc/class.'.$this->id.'.controller.php', $replacements);

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

