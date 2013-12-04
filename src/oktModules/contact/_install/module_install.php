<?php
/**
 * @ingroup okt_module_contact
 * @brief La classe d'installation du Module Contact
 *
 */

use Okatea\Modules\ModuleInstall;

class moduleInstall_contact extends ModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'contact_recipients',
		));
	}

	public function update()
	{
	}

} # class
