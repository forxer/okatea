<?php
/**
 * @ingroup okt_module_lbl_pirobox
 * @brief La classe d'installation du Module pirobox
 *
 */

use Tao\Modules\ModuleInstall;

class moduleInstall_lbl_pirobox extends ModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'pirobox_config',
		));
	}

} # class
