<?php
/**
 * @ingroup okt_module_galleries
 * @brief La classe d'installation du module galerie.
 *
 */

class moduleInstall_galleries extends oktModuleInstall
{
	public function installTestSet()
	{
		# since galleries 1.4 / core 0.8
		$this->checklist->addItem(
			'regenerate_galleries_thumbnails',
			$this->okt->galleries->tree->regenMinImages(),
			'Regeneration of galleries thumbnails',
			'Cannot regenerate galleries thumbnails'
		);
		$this->checklist->addItem(
			'regenerate_items_thumbnails',
			$this->okt->galleries->items->regenMinImages(),
			'Regeneration of items thumbnails',
			'Cannot regenerate items thumbnails'
		);
	}

	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'galleries',
			'galleries_manage',
			'galleries_add',
			'galleries_remove',
			'galleries_display'
		));
	}

	public function update()
	{
		# si version installée inférieure à 1.2
		if (version_compare($this->okt->galleries->version(), '1.2', '<'))
		{
			# migration des images des galeries
			$this->checklist->addItem(
				'galleries_images_migration',
				$this->doGalleriesImagesMigration(),
				'Migrate galleries images',
				'Cannot migrate galleries images'
			);

			# migration des images des éléments
			$this->checklist->addItem(
				'items_images_migration',
				$this->doItemsImagesMigration(),
				'Migrate items images',
				'Cannot migrate items images'
			);
		}

		# si version installée inférieure à 1.4
		if (version_compare($this->okt->galleries->version(), '1.4', '<'))
		{
			# reconstruction des tableaux d'images dans la base de données
			$this->checklist->addItem(
				'rebuild_galleries_images_array',
				$this->rebuildGalleriesImagesArray(),
				'Rebuild galleries images array',
				'Cannot galleries rebuild images array'
			);

			# reconstruction des tableaux d'images dans la base de données
			$this->checklist->addItem(
				'rebuild_items_images_array',
				$this->rebuildItemsImagesArray(),
				'Rebuild items images array',
				'Cannot items rebuild images array'
			);
		}

		# si version installée inférieure à 1.5
		if (version_compare($this->okt->galleries->version(), '1.5', '<'))
		{
			# update bases URL
			$this->checklist->addItem(
				'update_galleries_base_url',
				$this->updateBasesURL(),
				'Update bases URL',
				'Cannot update bases URL'
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

	protected function rebuildGalleriesImagesArray()
	{
		$rsGalleries = $this->okt->galleries->tree->getGalleries(array('active'=>2));

		# boucle sur les galeries
		while ($rsGalleries->fetch())
		{
			# si on as l'ancien format alors on met à jour
			if (!is_array($rsGalleries->image))
			{
				$aNewImages = $this->okt->galleries->tree->getImageUploadInstance()->buildImagesInfos($rsGalleries->id,array(1=>$rsGalleries->image));

				# mise à jour des images de la galerie
				$this->okt->galleries->tree->updImages($rsGalleries->id,$aNewImages[1]);
			}
		}

		return true;
	}

	protected function rebuildItemsImagesArray()
	{
		$rsItems = $this->okt->galleries->getItems(array('visibility'=>2));

		# boucle sur les éléments
		while ($rsItems->fetch())
		{
			# si on as l'ancien format alors on met à jour
			if (!is_array($rsItems->image))
			{
				$aNewImages = $this->okt->galleries->getItemImageUpload()->buildImagesInfos($rsItems->id,array(1=>$rsItems->image));

				# mise à jour des images de l'élément
				$this->okt->galleries->updItemImages($rsItems->id,$aNewImages[1]);
			}
		}

		return true;
	}

	protected function doGalleriesImagesMigration()
	{
		$rsGalleries = $this->okt->galleries->tree->getGalleries(array('active'=>2));

		$sRootImagesDir = $this->okt->galleries->upload_dir.'img/galleries/';

		# boucle sur les galeries
		while ($rsGalleries->fetch())
		{
			$sImage = $rsGalleries->image;

			$sNewName = '';

			if ($sImage != '' && file_exists($sRootImagesDir.$sImage))
			{
				$sExtension = files::getExtension($sImage);

				$sNewName = '1.'.$sExtension;

				# création du répertoire s'il existe pas
				if (!file_exists($sRootImagesDir.$rsGalleries->id)) {
					files::makeDir($sRootImagesDir.$rsGalleries->id,true);
				}

				# on déplacent l'image
				rename(
					$sRootImagesDir.$sImage,
					$sRootImagesDir.$rsGalleries->id.'/'.$sNewName
				);

				# on suppriment les miniatures
				if (file_exists($sRootImagesDir.'min-'.$sImage)) {
					unlink($sRootImagesDir.'min-'.$sImage);
				}

				if (file_exists($sRootImagesDir.'sq-'.$sImage)) {
					unlink($sRootImagesDir.'sq-'.$sImage);
				}
			}

			# mise à jour de l'image de la galerie
			$this->okt->galleries->tree->updImages($rsGalleries->id,$sNewName);
		}

		# régénération des miniatures
		$this->okt->galleries->tree->regenMinImages();

		return true;
	}

	protected function doItemsImagesMigration()
	{
		$rsItems = $this->okt->galleries->getItems(array('visibility'=>2));

		$sRootImagesDir = $this->okt->galleries->upload_dir.'img/items/';

		# boucle sur les éléments
		while ($rsItems->fetch())
		{
			$sImage = $rsItems->image;

			$sNewName = '';

			if ($sImage != '' && file_exists($sRootImagesDir.$sImage))
			{
				$sExtension = files::getExtension($sImage);

				$sNewName = '1.'.$sExtension;

				# création du répertoire s'il existe pas
				if (!file_exists($sRootImagesDir.$rsItems->id)) {
					files::makeDir($sRootImagesDir.$rsItems->id,true);
				}

				# on déplacent l'image
				rename(
					$sRootImagesDir.$sImage,
					$sRootImagesDir.$rsItems->id.'/'.$sNewName
				);

				# on suppriment les miniatures
				if (file_exists($sRootImagesDir.'min-'.$sImage)) {
					unlink($sRootImagesDir.'min-'.$sImage);
				}

				if (file_exists($sRootImagesDir.'sq-'.$sImage)) {
					unlink($sRootImagesDir.'sq-'.$sImage);
				}
			}

			# mise à jour de l'image de l'élément
			$this->okt->galleries->updItemImages($rsItems->id,$sNewName);
		}

		# régénération des miniatures
		$this->okt->galleries->items->regenMinImages();

		return true;
	}

	protected function updateBasesURL()
	{
		try
		{
			$config = $this->okt->newConfig('conf_galleries');
			$config->write(array(
				'public_gallery_url' => str_replace('/%s','',$this->okt->galleries->config->public_gallery_url),
				'public_item_url' => str_replace('/%s','',$this->okt->galleries->config->public_item_url)
			));
		}
		catch (InvalidArgumentException $e) {
			return false;
		}

		return true;
	}


	protected function updConfigI18n()
	{
		$oConfig = $this->okt->galleries->config;

		try
		{
			$config = $this->okt->newConfig('conf_galleries');
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
							'fr' => '/'.$oConfig->public_list_url,
							'en' => ''
					),
					'public_feed_url' => array(
							'fr' => '/'.$oConfig->public_feed_url,
							'en' => ''
					),
					'public_gallery_url' => array(
							'fr' => '/'.$oConfig->public_gallery_url,
							'en' => ''
					),
					'public_item_url' => array(
							'fr' => '/'.$oConfig->public_item_url,
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
