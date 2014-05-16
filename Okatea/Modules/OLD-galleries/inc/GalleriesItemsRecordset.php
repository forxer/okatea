<?php
/**
 * @ingroup okt_module_galleries
 * @brief Extension du recordset pour les élément des galeries
 *
 */
use Okatea\Tao\Database\Recordset;

class GalleriesItemsRecordset extends Recordset
{

	/**
	 * Okatea application instance.
	 * 
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * Défini l'instance de l'application qui sera passée à l'objet après
	 * qu'il ait été instancié.
	 *
	 * @param
	 *        	Okatea\Tao\Application okt Okatea application instance.
	 * @return void
	 */
	public function setCore($okt)
	{
		$this->okt = $okt;
	}

	/**
	 * Retourne l'URL publique de l'élément en cours.
	 *
	 * @return string
	 */
	public function getItemUrl($sLanguage = null)
	{
		return GalleriesHelpers::getItemUrl($this->slug, $sLanguage);
	}

	/**
	 * Retourne l'URL publique de la galerie de l'élément en cours.
	 *
	 * @return string
	 */
	public function getGalleryUrl($sLanguage = null)
	{
		return GalleriesHelpers::getGalleryUrl($this->gallery_slug, $sLanguage);
	}

	/**
	 * Retourne les informations de l'images de l'élément en cours en fonction des données de la BDD.
	 *
	 * @return array
	 */
	public function getImagesInfo()
	{
		return $this->getImagesArray();
	}

	/**
	 * Retourne le tableau d'images de l'élément en cours.
	 *
	 * @return array
	 */
	public function getImagesArray()
	{
		return array_filter((array) unserialize($this->image));
	}

	/**
	 * Retourne le chemin du répertoire des images de l'élément en cours.
	 *
	 * @return string
	 */
	public function getCurrentImagesDir()
	{
		return $this->okt->galleries->upload_dir . '/img/items/' . $this->id;
	}

	/**
	 * Retourne l'URL du répertoire des images de l'élément en cours.
	 *
	 * @return string
	 */
	public function getCurrentImagesUrl()
	{
		return $this->okt->galleries->upload_url . '/img/items/' . $this->id;
	}
}
