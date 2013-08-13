<?php
/**
 * @ingroup okt_module_catalog
 * @brief La classe d'installation du module.
 *
 */

class moduleInstall_catalog extends oktModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'catalog',
			'catalog_categories',
			'catalog_add',
			'catalog_remove',
			'catalog_display'
		));
	}

	public function installTestSet()
	{
		# since catalog 1.3 / core 0.8
		$this->checklist->addItem(
			'regenerate_thumbnails',
			$this->okt->catalog->regenMinImages(),
			'Regeneration of thumbnails',
			'Cannot regenerate thumbnails'
		);
	}

	public function update()
	{
		# si version installée inférieure à 1.2
		if (version_compare($this->okt->catalog->version(), '1.2', '<'))
		{
			# migration des images
			$this->checklist->addItem(
				'move_images_in_proper_directory',
				$this->moveImagesInProperDirectory(),
				'Move images in proper directory',
				'Cannot move images in proper directory'
			);
		}

		# si version installée inférieure à 1.3
		if (version_compare($this->okt->catalog->version(), '1.3', '<'))
		{
			# reconstruction des tableaux d'images dans la base de données
			$this->checklist->addItem(
				'rebuild_images_array',
				$this->rebuildImagesArray(),
				'Rebuild images array',
				'Cannot rebuild images array'
			);
		}

		# si version installée inférieure à 1.4
		if (version_compare($this->okt->catalog->version(), '1.4', '<'))
		{
			# reconstruction des index de recherche
			$this->checklist->addItem(
				'rebuild_catalog_products_search_indexes',
				$this->indexAllProducts(),
				'Rebuild products search indexes',
				'Cannot rebuild products search indexes'
			);
		}

		# si version installée inférieure à 1.6
		if (version_compare($this->okt->catalog->version(), '1.6', '<'))
		{
			# update products base URL
			$this->checklist->addItem(
				'update_products_base_url',
				$this->updateProductsBaseURL(),
				'Update products base URL',
				'Cannot update products base URL'
			);		
		}
	}

	protected function rebuildImagesArray()
	{
		$rsProds = $this->okt->catalog->getProds();

		# boucle sur les produits
		while ($rsProds->fetch())
		{
			# images actuelles du produit
			$aImages = $rsProds->getImagesArray();

			# si on as l'ancien format alors on met à jour
			if (isset($aImages[1]) && !is_array($aImages[1]))
			{
				$aNewImages = $this->okt->catalog->getImageUpload()->buildImagesInfos($rsProds->id,$aImages);

				# mise à jour des images du produit
				$this->okt->catalog->updProdImages($rsProds->id,$aNewImages);
			}
		}

		return true;
	}

	protected function moveImagesInProperDirectory()
	{
		$rsProds = $this->okt->catalog->getProds();

		$root_images_dir = $this->okt->catalog->upload_dir.'img/';

		# boucle sur les produits
		while ($rsProds->fetch())
		{
			# images actuelles du produit
			$aImages = array_filter((array)unserialize($rsProds->images));

			$aNewImages = array();

			# on augmentent le nombre d'images total pour bien déplacer toutes les images
			# même si la configuration a changée en cours de route
			$num_images = $this->okt->catalog->config->images['number']+5;

			# boucle sur les éventuelles images du produit
			for ($i=1; $i<=$num_images; $i++)
			{
				if (isset($aImages[$i]) && file_exists($root_images_dir.$aImages[$i]))
				{
					$sExtension = files::getExtension($aImages[$i]);

					$sNewName = $i.'.'.$sExtension;

					# création du répertoire s'il existe pas
					if (!file_exists($root_images_dir.$rsProds->id)) {
						files::makeDir($root_images_dir.$rsProds->id,true);
					}

					# on déplacent l'image
					rename(
						$root_images_dir.$aImages[$i],
						$root_images_dir.$rsProds->id.'/'.$sNewName
					);

					# on suppriment les miniatures
					if (file_exists($root_images_dir.'min-'.$aImages[$i])) {
						unlink($root_images_dir.'min-'.$aImages[$i]);
					}

					if (file_exists($root_images_dir.'sq-'.$aImages[$i])) {
						unlink($root_images_dir.'sq-'.$aImages[$i]);
					}

					$aNewImages[$i] = $sNewName;
				}
			}

			# mise à jour des images du produit
			$this->okt->catalog->updProdImages($rsProds->id,$aNewImages);
		}

		# régénération des miniatures
		$this->okt->catalog->regenMinImages();

		return true;
	}

	protected function indexAllProducts()
	{
		return $this->okt->catalog->indexAllProducts();
	}
	
	protected function updateProductsBaseURL()
	{
		try
		{
			$config = $this->okt->newConfig('conf_catalog');
			$config->write(array(
				'public_product_url' => str_replace('/%s','',$this->okt->catalog->config->public_product_url)
			));
		}
		catch (InvalidArgumentException $e)
		{
			return false;
		}

		return true;	
	}

} # class
