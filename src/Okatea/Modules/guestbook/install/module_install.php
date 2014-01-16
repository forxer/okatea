<?php
/**
 * @ingroup okt_module_guestbook
 * @brief La classe d'installation du module guestbook.
 *
 */

use Okatea\Tao\Modules\Manage\Process as ModuleInstall;

class moduleInstall_guestbook extends ModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'guestbook'
		));
	}

	public function update()
	{
	}

}
