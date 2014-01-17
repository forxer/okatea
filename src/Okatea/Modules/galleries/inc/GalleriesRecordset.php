<?php
/**
 * @ingroup okt_module_galleries
 * @brief Extension du recordset pour les galeries
 *
 */

use Okatea\Tao\Database\Recordset;

class GalleriesRecordset extends Recordset
{
	/**
	 * Okatea application instance.
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * Défini l'instance de l'application qui sera passée à l'objet après
	 * qu'il ait été instancié.
	 *
	 * @param Okatea\Tao\Application okt 	Okatea application instance.
	 * @return void
	 */
	public function setCore($okt)
	{
		$this->okt = $okt;
	}

	/**
	 * Retourne l'URL publique d'une galerie
	 *
	 * @param string $sLanguage
	 * @return string
	 */
	public function getGalleryUrl($sLanguage=null)
	{
		return GalleriesHelpers::getGalleryUrl($this->slug, $sLanguage);
	}

	/**
	 * Retourne les informations de l'images d'un élément en fonction des données de la BDD
	 *
	 * @return 	array
	 */
	public function getImagesInfo()
	{
		return $this->getImagesArray();
	}

	public function getImagesArray()
	{
		return array_filter((array)unserialize($this->image));
	}

	public function getCurrentImagesDir()
	{
		return $this->okt->galleries->upload_dir.'img/galleries/'.$this->id.'/';
	}

	public function getCurrentImagesUrl()
	{
		return $this->okt->galleries->upload_url.'img/galleries/'.$this->id.'/';
	}

}
