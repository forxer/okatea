<?php
/**
 * @ingroup okt_module_media_manager
 * @brief La classe d'installation du Module Gestionnaire de médias
 *
 */

use Tao\Modules\Manage\Process as ModuleInstall;

class moduleInstall_media_manager extends ModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'media',
			'media_admin'
		));
	}

} # class
