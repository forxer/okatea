<?php
/**
 * @ingroup okt_module_accessible_captcha
 * @brief La classe d'installation du Module captcha accessible
 *
 */
use Okatea\Tao\Modules\Manage\Process as ModuleInstall;

class moduleInstall_accessible_captcha extends ModuleInstall
{

	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'accessible_captcha_config'
		));
	}
}
