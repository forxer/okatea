<?php
/**
 * @ingroup okt_module_lbl_nyromodal
 * @brief La classe d'installation du Module nyromodal
 *
 */

use Okatea\Tao\Modules\Manage\Process as ModuleInstall;

class moduleInstall_lbl_nyromodal extends ModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'nyromodal_config',
		));
	}

}
