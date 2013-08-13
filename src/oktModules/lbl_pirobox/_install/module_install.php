<?php
/**
 * @ingroup okt_module_lbl_pirobox
 * @brief La classe d'installation du Module pirobox
 *
 */

class moduleInstall_lbl_pirobox extends oktModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'pirobox_config',
		));
	}

} # class
