<?php
/**
 * @ingroup okt_module_lbl_fancybox
 * @brief La classe d'installation du Module Fancybox
 *
 */

class moduleInstall_lbl_fancybox extends oktModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'fancybox_config',
		));
	}

} # class
