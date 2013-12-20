<?php
/**
 * @ingroup okt_module_lbl_nyromodal_2
 * @brief La classe d'installation du Module nyromodal 2
 *
 */

use Tao\Modules\Manage\Process as ModuleInstall;

class moduleInstall_lbl_nyromodal_2 extends ModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'nyromodal_2_config',
		));
	}

}
