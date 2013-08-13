<?php
/**
 * @ingroup okt_module_lbl_colorbox
 * @brief La classe d'installation du Module Colorbox
 *
 */

class moduleInstall_lbl_colorbox extends oktModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'colorbox_config',
		));
	}

} # class
