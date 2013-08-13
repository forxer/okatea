<?php
/**
 * @ingroup okt_module_lbl_nyromodal
 * @brief La classe d'installation du Module nyromodal
 *
 */

class moduleInstall_lbl_nyromodal extends oktModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'nyromodal_config',
		));
	}

} # class
