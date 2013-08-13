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
		# si version installée inférieure à 1.5
		if (version_compare($this->okt->contact->version(), '1.5', '<'))
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
		$oConfig = $this->okt->contact->config;

		try
		{
			$config = $this->okt->newConfig('conf_contact');
			$config->write(array(
				'name' => array(
					'fr' => $oConfig->name,
					'en' => ''
				),
				'title' => array(
					'fr' =>  $oConfig->title,
					'en' => ''
				),
				'meta_description' => array(
					'fr' =>  $oConfig->meta_description,
					'en' => ''
				),
				'meta_keywords' => array(
					'fr' =>  $oConfig->meta_keywords,
					'en' => ''
				),
				'public_url' => array(
					'fr' => '/'.$oConfig->public_url,
					'en' => ''
				),
				'public_map_url' => array(
					'fr' => '/'.$oConfig->public_map_url,
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
