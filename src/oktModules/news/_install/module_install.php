<?php
/**
 * @ingroup okt_module_news
 * @brief La classe d'installation du Module News.
 *
 */

class moduleInstall_news extends oktModuleInstall
{
	public function installTestSet()
	{
		# since news 1.5 / core 0.8
		$this->checklist->addItem(
			'regenerate_thumbnails',
			$this->okt->news->regenMinImages(),
			'Regeneration of thumbnails',
			'Cannot regenerate thumbnails'
		);
	}

	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'news_usage',
			'news_publish',
			'news_delete',
			'news_contentadmin',
			'news_rubriques',
			'news_display'
		));
	}

	public function update()
	{
		# si version installée inférieure à 1.3
		if (version_compare($this->okt->news->version(), '1.3', '<'))
		{
			# migration des images
			$this->checklist->addItem(
				'move_images_in_proper_directory',
				$this->moveImagesInProperDirectory(),
				'Move images in proper directory',
				'Cannot move images in proper directory'
			);
		}

		# si version installée inférieure à 1.5
		if (version_compare($this->okt->news->version(), '1.5', '<'))
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
		if (version_compare($this->okt->news->version(), '1.6', '<'))
		{
			# reconstruction des index de recherche
			$this->checklist->addItem(
				'rebuild_posts_search_indexes',
				$this->indexAllPosts(),
				'Rebuild posts search indexes',
				'Cannot rebuild posts search indexes'
			);
		}

		# si version installée inférieure à 2.0
		if (version_compare($this->okt->news->version(), '2.0', '<'))
		{
			# update news base URL
			$this->checklist->addItem(
				'update_posts_base_url',
				$this->updatePostsBaseURL(),
				'Update posts base URL',
				'Cannot update posts base URL'
			);

			# maj config fort i18n
			$this->checklist->addItem(
				'update_config_for_i18n',
				$this->updConfigI18n(),
				'Update config for i18n support',
				'Cannot update config for i18n support'
			);

			# move templates for multiples
			$this->checklist->addItem(
				'update_move_tpl',
				$this->moveTemplatesForMultiples(),
				'Move templates files for multiples templates support',
				'Cannot move templates files for multiples templates support'
			);
		}
	}


	protected function moveImagesInProperDirectory()
	{
		$rsPosts = $this->okt->news->getPosts();

		$root_images_dir = $this->okt->news->upload_dir.'img/';

		# boucle sur les posts
		while ($rsPosts->fetch())
		{
			# images actuelles du post
			$aImages = $rsPosts->getImagesArray();

			$aNewImages = array();

			# on augmentent le nombre d'images total pour bien déplacer toutes les images
			# même si la configuration a changée en cours de route
			$num_images = $this->okt->news->config->images['number']+5;

			# boucle sur les éventuelles images du post
			for ($i=1; $i<=$num_images; $i++)
			{
				if (isset($aImages[$i]) && file_exists($root_images_dir.$aImages[$i]))
				{
					$sExtension = files::getExtension($aImages[$i]);

					$sNewName = $i.'.'.$sExtension;

					# création du répertoire s'il existe pas
					if (!file_exists($root_images_dir.$rsPosts->id)) {
					files::makeDir($root_images_dir.$rsPosts->id,true);
					}

					# on déplace l'image
					rename(
							$root_images_dir.$aImages[$i],
							$root_images_dir.$rsPosts->id.'/'.$sNewName
							);

							# on supprime les miniatures
							if (file_exists($root_images_dir.'min-'.$aImages[$i])) {
							unlink($root_images_dir.'min-'.$aImages[$i]);
					}

					if (file_exists($root_images_dir.'sq-'.$aImages[$i])) {
						unlink($root_images_dir.'sq-'.$aImages[$i]);
					}

					$aNewImages[$i] = $sNewName;
				}
			}

			# mise à jour des images du post
			$this->okt->news->updPostImages($rsPosts->id,$aNewImages);
		}

		# régénération des miniatures
		$this->okt->news->regenMinImages();

		return true;
	}

	protected function rebuildImagesArray()
	{
		$rsPosts = $this->okt->news->getPostsRecordset(array('active' => 2));

		# boucle sur les news
		while ($rsPosts->fetch())
		{
			# images actuelles de la page
			$aImages = $rsPosts->getImagesArray();

			# si on as l'ancien format alors on met à jour
			if (isset($aImages[1]) && !is_array($aImages[1]))
			{
				$aNewImages = $this->okt->news->getImageUpload()->buildImagesInfos($rsPosts->id,$aImages);

				# mise à jour des images de la page
				$this->okt->news->updImagesInDb($rsPosts->id,$aNewImages);
			}
		}

		return true;
	}

	protected function indexAllPosts()
	{
		return $this->okt->news->indexAllPosts();
	}

	protected function updatePostsBaseURL()
	{
		try
		{
			$config = $this->okt->newConfig('conf_news');
			$config->write(array(
				'public_post_url' => str_replace('/%s','',$this->okt->news->config->public_post_url)
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
		$oConfig = $this->okt->news->config;

		try
		{
			$config = $this->okt->newConfig('conf_news');

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
				'public_post_url' => array(
					'fr' => $oConfig->public_post_url,
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
			if (file_exists(OKT_THEMES_PATH.'/'.$sThemeId.'/templates/news_item_tpl.php'))
			{
				files::makeDir(OKT_THEMES_PATH.'/'.$sThemeId.'/templates/news/item/default',true);

				rename(
					OKT_THEMES_PATH.'/'.$sThemeId.'/templates/news_item_tpl.php',
					OKT_THEMES_PATH.'/'.$sThemeId.'/templates/news/item/default/template.php'
				);
			}

			if (file_exists(OKT_THEMES_PATH.'/'.$sThemeId.'/templates/news_list_tpl.php'))
			{
				files::makeDir(OKT_THEMES_PATH.'/'.$sThemeId.'/templates/news/list/default',true);

				rename(
					OKT_THEMES_PATH.'/'.$sThemeId.'/templates/news_list_tpl.php',
					OKT_THEMES_PATH.'/'.$sThemeId.'/templates/news/list/default/template.php'
				);
			}

			if (file_exists(OKT_THEMES_PATH.'/'.$sThemeId.'/templates/news_rss_tpl.php'))
			{
				files::makeDir(OKT_THEMES_PATH.'/'.$sThemeId.'/templates/news/feed/rss',true);

				rename(
					OKT_THEMES_PATH.'/'.$sThemeId.'/templates/news_rss_tpl.php',
					OKT_THEMES_PATH.'/'.$sThemeId.'/templates/news/feed/rss/template.php'
				);
			}
		}

		return true;
	}

} # class
