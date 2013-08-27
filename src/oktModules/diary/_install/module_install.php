<?php
/**
 * @ingroup okt_module_diary
 * @brief La classe d'installation du Module diary
 *
 */

class moduleInstall_diary extends oktModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'diary',
			'diary_add',
			'diary_remove',
			'diary_display'
		));
	}

	public function update()
	{
	}

} # class
