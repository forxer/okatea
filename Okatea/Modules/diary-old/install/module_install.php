<?php
/**
 * @ingroup okt_module_diary
 * @brief La classe d'installation du Module diary
 *
 */

use Okatea\Tao\Modules\Manage\Process as ModuleInstall;

class moduleInstall_diary extends ModuleInstall
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

}
