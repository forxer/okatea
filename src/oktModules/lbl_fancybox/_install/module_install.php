<?php
/**
 * @ingroup okt_module_lbl_fancybox
 * @brief La classe d'installation du Module Fancybox
 *
 */

use Tao\Modules\ModuleInstall;

class moduleInstall_lbl_fancybox extends ModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'fancybox_config',
		));
	}

} # class
