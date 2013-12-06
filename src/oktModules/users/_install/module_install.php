<?php
/**
 * @ingroup okt_module_users
 * @brief La classe d'installation du module véhicules.
 *
 */

use Okatea\Modules\ModuleInstall;

class moduleInstall_users extends ModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'users',
			'users_edit',
			'users_delete',
			'users_private_space',
			'change_password',
			'users_display'
		));
	}

	public function update()
	{
	}

} # class
