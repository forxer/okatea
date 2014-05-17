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
	public function __construct()
	{
		parent::__construct($this->okt, $this->okt->module('Pages')->config->images);

		$this->setConfig(array(
			'upload_dir' => $this->okt->module('Pages')->upload_dir . '/img',
			'upload_url' => $this->okt->module('Pages')->upload_url . '/img'
		));
	}

	/**
	 * Retourne une instance de la classe oktImageUpload
	 *
	 * @return object oktImageUpload
	 */
	public function getImageUpload()
	{
		$o = new ImageUpload($this->okt, $this->okt->module('Pages')->config->images);
		$o->setConfig(array(
			'upload_dir' => $this->upload_dir . '/img',
			'upload_url' => $this->upload_url . '/img'
		));

		return $o;
	}

	/**
	 * Ajout d'image(s) à une page donnée
	 *
	 * @param
	 *        	$iPageId
	 * @return boolean
	 */
	public function addImages($iPageId)
	{
		if (! $this->okt->module('Pages')->config->images['enable'])
		{
			return null;
		}

		$aImages = $this->getImageUpload()->addImages($iPageId);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		return $this->updImagesInDb($iPageId, $aImages);
	}

	/**
	 * Modification d'image(s) d'une page donnée
	 *
	 * @param
	 *        	$iPageId
	 * @return boolean
	 */
	public function updImages($iPageId)
	{
		if (! $this->okt->module('Pages')->config->images['enable'])
		{
			return null;
		}

		$aCurrentImages = $this->getImagesFromDb($iPageId);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		$aImages = $this->getImageUpload()->updImages($iPageId, $aCurrentImages);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		return $this->updImagesInDb($iPageId, $aImages);
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
	public function deleteImage($iPageId, $img_id)
	{
		$aCurrentImages = $this->getImagesFromDb($iPageId);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		$aNewImages = $this->getImageUpload()->deleteImage($iPageId, $aCurrentImages, $img_id);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		return $this->updImagesInDb($iPageId, $aNewImages);
	}

	/**
	 * Suppression des images d'une page donnée
	 *
	 * @param
	 *        	$iPageId
	 * @return boolean
	 */
	public function deleteImages($iPageId)
	{
		$aCurrentImages = $this->getImagesFromDb($iPageId);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		$this->getImageUpload()->deleteAllImages($iPageId, $aCurrentImages);

		return $this->updImagesInDb($iPageId);
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

		$rsPages = $this->getPages(array(
			'active' => 2
		));

		while ($rsPages->fetch())
		{
			$aImages = $rsPages->getImagesInfo();
			$aImagesList = array();

			foreach ($aImages as $key => $image)
			{
				$this->getImageUpload()->buildThumbnails($rsPages->id, $image['img_name']);

				$aImagesList[$key] = array_merge($aImages[$key], $this->getImageUpload()->buildImageInfos($rsPages->id, $image['img_name']));
			}

			$this->updImagesInDb($rsPages->id, $aImagesList);
		}

		return true;
	}

	/**
	 * Récupère la liste des images d'une page donnée
	 *
	 * @param
	 *        	$iPageId
	 * @return array
	 */
	public function getImagesFromDb($iPageId)
	{
		if (! $this->pageExists($iPageId))
		{
			$this->error->set(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
			return false;
		}

		$rsPage = $this->getPagesRecordset(array(
			'id' => $iPageId
		));

		$aImages = $rsPage->images ? unserialize($rsPage->images) : array();

		return $aImages;
	}

	/**
	 * Met à jours la liste des images d'une page donnée
	 *
	 * @param array $iPageId
	 * @param
	 *        	$aImages
	 * @return boolean
	 */
	public function updImagesInDb($iPageId, $aImages = array())
	{
		if (! $this->pageExists($iPageId))
		{
			$this->error->set(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
			return false;
		}

		$aImages = ! empty($aImages) ? serialize($aImages) : NULL;

		$sQuery = 'UPDATE ' . $this->t_pages . ' SET ' . 'images=' . (! is_null($aImages) ? '\'' . $this->db->escapeStr($aImages) . '\'' : 'NULL') . ' ' . 'WHERE id=' . (integer) $iPageId;

		if (! $this->db->execute($sQuery))
		{
			return false;
		}

		return true;
	}
}
