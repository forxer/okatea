<?php
/**
 * @ingroup okt_module_media_manager
 * @brief La classe d'installation du Module Gestionnaire de médias
 *
 */

class moduleInstall_media_manager extends oktModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'media',
			'media_admin'
		));
	}

} # class
