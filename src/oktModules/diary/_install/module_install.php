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
		# si version installée inférieure à 1.1
		if (version_compare($this->okt->diary->version(), '1.1', '<'))
		{
			# update events base URL
			$this->checklist->addItem(
				'update_events_base_url',
				$this->updateEventsBaseURL(),
				'Update events base URL',
				'Cannot update events base URL'
			);
			# maj config fort i18n
			$this->checklist->addItem(
				'update_config_for_i18n',
				$this->updConfigI18n(),
				'Update config for i18n support',
				'Cannot update config for i18n support'
			);
		}
	}

	protected function updateEventsBaseURL()
	{
		try
		{
			$config = $this->okt->newConfig('conf_diary');
			$config->write(array(
				'public_event_url' => str_replace('/%s','',$this->okt->diary->config->public_event_url)
			));
		}
		catch (InvalidArgumentException $e)
		{
			return false;
		}

		return true;
	}

	protected function updConfigI18n()
	{
		$oConfig = $this->okt->diary->config;

		try
		{
			$config = $this->okt->newConfig('conf_diary');
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
				'public_list_url' => array(
					'fr' => $oConfig->public_list_url,
					'en' => ''
				),
				'public_event_url' => array(
					'fr' => $oConfig->public_event_url,
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
