<?php
/**
 * @ingroup okt_module_contact
 * @brief La classe d'installation du Module Contact
 *
 */

class moduleInstall_contact extends oktModuleInstall
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
