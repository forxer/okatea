<?php
/**
 * @ingroup okt_module_guestbook
 * @brief La classe d'installation du module guestbook.
 *
 */

class moduleInstall_guestbook extends oktModuleInstall
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

} # class
