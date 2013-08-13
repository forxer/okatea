<?php
/**
 * @ingroup okt_module_faq
 * @brief La classe d'installation du module faq.
 *
 */

class moduleInstall_faq extends oktModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'faq',
			'faq_add',
			'faq_remove',
			'faq_categories',
			'faq_display'
		));
	}
	
	public function update()
	{
		# si version installée inférieure à 1.1
		if (version_compare($this->okt->faq->version(), '1.1', '<'))
		{
			# update questions base URL
			$this->checklist->addItem(
				'update_questions_base_url',
				$this->updateQuestionsBaseURL(),
				'Update questions base URL',
				'Cannot update questions base URL'
			);		
		}
	}
	
	protected function updateQuestionsBaseURL()
	{
		$oConfig = $this->okt->faq->config;
		
		$aNewData = array();
		
		foreach ($oConfig->public_question_url as $k=>$v) {
			$aNewData[$k] = str_replace('/%s','',$v);
		}

		try
		{
			$config = $this->okt->newConfig('conf_faq');
			$config->write(array(
				'public_question_url' => $aNewData
			));
		}
		catch (InvalidArgumentException $e)
		{
			return false;
		}

		return true;	
	}

} # class
