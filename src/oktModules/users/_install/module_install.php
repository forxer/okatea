<?php
/**
 * @ingroup okt_module_users
 * @brief La classe d'installation du module véhicules.
 *
 */

class moduleInstall_users extends oktModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
//			'users',
//			'users_edit',
//			'users_delete',
//			'users_private_space',
			'change_password',
//			'users_display'
		));
	}
	
	public function update()
	{
		# si version installée inférieure à 1.5
		if (version_compare($this->okt->users->version(), '1.5', '<'))
		{
			# maj config fort i18n
			$this->checklist->addItem(
				'update_config_for_i18n',
				$this->updConfigI18n(),
				'Update config for i18n support',
				'Cannot update config for i18n support'
			);
		}
	}
	
	protected function updConfigI18n()
	{
		$oConfig = $this->okt->users->config;

		try
		{
			$config = $this->okt->newConfig('conf_users');
			$config->write(array(
				'public_login_page' => array(
					'fr' => $oConfig->public_login_page,
					'en' => ''
				),
				'public_logout_page' => array(
					'fr' =>  $oConfig->public_logout_page,
					'en' => ''
				),
				'public_register_page' => array(
					'fr' =>  $oConfig->public_register_page,
					'en' => ''
				),
				'public_log_reg_page' => array(
					'fr' =>  $oConfig->public_log_reg_page,
					'en' => ''
				),
				'public_forget_password_page' => array(
					'fr' => $oConfig->public_forget_password_page,
					'en' => ''
				),
				'public_profile_page' => array(
					'fr' => $oConfig->public_profile_page,
					'en' => ''
				)
			));
		}
		catch (InvalidArgumentException $e)
		{
			return false;
		}

		return true;
	}

} # class
