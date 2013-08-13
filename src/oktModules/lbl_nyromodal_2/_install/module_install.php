<?php
/**
 * @ingroup okt_module_lbl_nyromodal_2
 * @brief La classe d'installation du Module nyromodal 2
 *
 */

class moduleInstall_lbl_nyromodal_2 extends oktModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'nyromodal_2_config',
		));
	}

} # class
