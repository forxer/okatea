<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Pages;

use Okatea\Tao\Images\ImageUpload;

class PagesImages extends ImageUpload
{
	protected $modulePages;

	protected $pages;

	public function __construct($okt)
	{
		parent::__construct($okt, $okt->module('Pages')->config->images);

		$this->modulePages = $this->okt->module('Pages');
		$this->pages = $this->okt->module('Pages')->pages;

		$this->setConfig(array(
			'upload_dir' => $this->modulePages->upload_dir . '/img',
			'upload_url' => $this->modulePages->upload_url . '/img'
		));
	}

	/**
	 * Ajout d'image(s) à une page donnée
	 *
	 * @param
	 *        	$iPageId
	 * @return boolean
	 */
	public function add($iPageId)
	{
		if (! $this->modulePages->config->images['enable'])
		{
			return null;
		}

		$aImages = $this->addImages($iPageId);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		return $this->pages->updImagesInDb($iPageId, $aImages);
	}

	/**
	 * Modification d'image(s) d'une page donnée
	 *
	 * @param
	 *        	$iPageId
	 * @return boolean
	 */
	public function update($iPageId)
	{
		if (! $this->modulePages->config->images['enable'])
		{
			return null;
		}

		$aCurrentImages = $this->pages->getImagesFromDb($iPageId);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		$aImages = $this->updImages($iPageId, $aCurrentImages);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		return $this->pages->updImagesInDb($iPageId, $aImages);
	}

	/**
	 * Suppression d'une image donnée d'une page donnée
	 *
	 * @param
	 *        	$iPageId
	 * @param
	 *        	$img_id
	 * @return boolean
	 */
	public function delete($iPageId, $img_id)
	{
		$aCurrentImages = $this->pages->getImagesFromDb($iPageId);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		$aNewImages = $this->deleteImage($iPageId, $aCurrentImages, $img_id);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		return $this->pages->updImagesInDb($iPageId, $aNewImages);
	}

	/**
	 * Suppression des images d'une page donnée
	 *
	 * @param
	 *        	$iPageId
	 * @return boolean
	 */
	public function deleteAll($iPageId)
	{
		$aCurrentImages = $this->pages->getImagesFromDb($iPageId);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		$this->deleteAllImages($iPageId, $aCurrentImages);

		return $this->pages->updImagesInDb($iPageId);
	}

	/**
	 * Régénération de toutes les miniatures des images
	 *
	 * @return void
	 */
	public function regenMinImages()
	{
		@ini_set('memory_limit', - 1);
		set_time_limit(0);

		$rsPages = $this->pages->getPages(array(
			'active' => 2
		));

		while ($rsPages->fetch())
		{
			$aImages = $rsPages->getImagesInfo();
			$aImagesList = array();

			foreach ($aImages as $key => $image)
			{
				$this->buildThumbnails($rsPages->id, $image['img_name']);

				$aImagesList[$key] = array_merge($aImages[$key], $this->buildImageInfos($rsPages->id, $image['img_name']));
			}

			$this->pages->updImagesInDb($rsPages->id, $aImagesList);
		}

		return true;
	}
}
