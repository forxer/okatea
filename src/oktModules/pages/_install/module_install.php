<?php
/**
 * @ingroup okt_module_pages
 * @brief La classe d'installation du Module Pages.
 *
 */

class moduleInstall_pages extends oktModuleInstall
{
	public function installTestSet()
	{
		# since pages 1.5 / core 0.8
		$this->checklist->addItem(
			'regenerate_thumbnails',
			$this->okt->pages->regenMinImages(),
			'Regeneration of thumbnails',
			'Cannot regenerate thumbnails'
		);
	}

	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'pages',
			'pages_categories',
			'pages_add',
			'pages_remove',
			'pages_display'
		));
	}

	public function update()
	{
		# si version installée inférieure à 1.5
		if (version_compare($this->okt->pages->version(), '1.5', '<'))
		{
			# reconstruction des tableaux d'images dans la base de données
			$this->checklist->addItem(
				'rebuild_images_array',
				$this->rebuildImagesArray(),
				'Rebuild images array',
				'Cannot rebuild images array'
			);
		}
		# si version installée inférieure à 1.6
		if (version_compare($this->okt->pages->version(), '1.6', '<'))
		{
			# reconstruction des index de recherche
			$this->checklist->addItem(
				'rebuild_pages_search_indexes',
				$this->indexAllPages(),
				'Rebuild pages search indexes',
				'Cannot rebuild pages search indexes'
			);
		}

		# si version installée inférieure à 2.0
		if (version_compare($this->okt->pages->version(), '2.0', '<'))
		{
			# update pages base URL
			$this->checklist->addItem(
				'update_pages_base_url',
				$this->updatePagesBaseURL(),
				'Update pages base URL',
				'Cannot update pages base URL'
			);

			# maj config fort i18n
			$this->checklist->addItem(
				'update_config_for_i18n',
				$this->updConfigI18n(),
				'Update config for i18n support',
				'Cannot update config for i18n support'
			);

			# maj config fort i18n
			$this->checklist->addItem(
				'update_move_tpl',
				$this->moveTemplatesForMultiples(),
				'Move templates files for multiples templates support',
				'Cannot move templates files for multiples templates support'
			);
		}


	}

	protected function rebuildImagesArray()
	{
		$rsPages = $this->okt->pages->getPages(array('active'=>2));

		# boucle sur les pages
		while ($rsPages->fetch())
		{
			# images actuelles de la page
			$aImages = $rsPages->getImagesArray();

			# si on as l'ancien format alors on met à jour
			if (isset($aImages[1]) && !is_array($aImages[1]))
			{
				$aNewImages = $this->okt->pages->getImageUpload()->buildImagesInfos($rsPages->id,$aImages);

				# mise à jour des images de la page
				$this->okt->pages->updImagesInDb($rsPages->id,$aNewImages);
			}
		}

		return true;
	}

	protected function indexAllPages()
	{
		return $this->okt->pages->indexAllPages();
	}

	protected function updatePagesBaseURL()
	{
		try
		{
			$config = $this->okt->newConfig('conf_pages');
			$config->write(array(
				'public_page_url' => str_replace('/%s','',$this->okt->pages->config->public_page_url)
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
		$oConfig = $this->okt->pages->config;

		try
		{
			$config = $this->okt->newConfig('conf_pages');

			if (is_array($oConfig->name)) {
				return null;
			}

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
				'public_feed_url' => array(
					'fr' => $oConfig->public_feed_url,
					'en' => ''
				),
				'public_page_url' => array(
					'fr' => $oConfig->public_page_url,
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

	protected function moveTemplatesForMultiples()
	{
		$oThemes = new oktThemes($this->okt, OKT_THEMES_PATH);
		$aThemes = $oThemes->getThemesList(true);

		foreach ($aThemes as $sThemeId=>$aThemeInfos)
		{
			if (file_exists(OKT_THEMES_PATH.'/'.$sThemeId.'/templates/pages_item_tpl.php'))
			{
				files::makeDir(OKT_THEMES_PATH.'/'.$sThemeId.'/templates/pages/item/default',true);

				rename(
					OKT_THEMES_PATH.'/'.$sThemeId.'/templates/pages_item_tpl.php',
					OKT_THEMES_PATH.'/'.$sThemeId.'/templates/pages/item/default/template.php'
				);
			}

			if (file_exists(OKT_THEMES_PATH.'/'.$sThemeId.'/templates/pages_list_tpl.php'))
			{
				files::makeDir(OKT_THEMES_PATH.'/'.$sThemeId.'/templates/pages/list/default',true);

				rename(
					OKT_THEMES_PATH.'/'.$sThemeId.'/templates/pages_list_tpl.php',
					OKT_THEMES_PATH.'/'.$sThemeId.'/templates/pages/list/default/template.php'
				);
			}

			if (file_exists(OKT_THEMES_PATH.'/'.$sThemeId.'/templates/pages_rss_tpl.php'))
			{
				files::makeDir(OKT_THEMES_PATH.'/'.$sThemeId.'/templates/pages/feed/rss',true);

				rename(
					OKT_THEMES_PATH.'/'.$sThemeId.'/templates/pages_rss_tpl.php',
					OKT_THEMES_PATH.'/'.$sThemeId.'/templates/pages/feed/rss/template.php'
				);
			}
		}

		return true;
	}

} # class
