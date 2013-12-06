<?php
/**
 * @ingroup okt_module_lbl_colorbox
 * @brief La classe d'installation du Module Colorbox
 *
 */

use Okatea\Modules\ModuleInstall;

class moduleInstall_lbl_colorbox extends ModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'colorbox_config',
		));
	}

} # class
