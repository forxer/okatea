<?php
/**
 * @ingroup okt_module_galleries
 * @brief Extension du recordset pour les galeries
 *
 */

class galleriesRecordset extends recordset
{
	/**
	 * L'objet oktCore
	 * @access private
	 * @var object
	 */
	private $okt;

	/**
	 * Défini l'objet de type oktCore qui sera passé à la classe après
	 * qu'elle ait été instanciée.
	 *
	 * @param oktCore okt 	Objet de type core
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
		return galleriesHelpers::getGalleryUrl($this->slug, $sLanguage);
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

} # class
