<?php
/**
 * @ingroup okt_module_menus
 * @brief La classe d'installation du Module Menus.
 *
 */

class moduleInstall_menus extends oktModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'menus_usage'
		));
	}

} # class
