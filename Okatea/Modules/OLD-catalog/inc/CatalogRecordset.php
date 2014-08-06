<?php
/**
 * @ingroup okt_module_catalog
 * @brief Extension du recordset pour les produits
 *
 */
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Database\Recordset;

class CatalogRecordset extends Recordset
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
	 * Returns whether post is editable.
	 *
	 * @return boolean
	 */
	public function isEditable()
	{
		# If user is admin or contentadmin, true
		if ($this->okt['visitor']->checkPerm('catalog'))
		{
			return true;
		}
		
		return false;
	}

	/**
	 * Returns whether post is deletable
	 *
	 * @return boolean
	 */
	public function isDeletable()
	{
		# If user is admin, or contentadmin, true
		if ($this->okt['visitor']->checkPerm('catalog_remove'))
		{
			return true;
		}
		
		return false;
	}

	/**
	 * Retourne l'URL publique d'un produit
	 *
	 * @return string
	 */
	public function getProductUrl($sLanguage = null)
	{
		return CatalogHelpers::getProductUrl($this->slug, $sLanguage);
	}

	/**
	 * Retourne l'URL publique d'une catégorie
	 *
	 * @return string
	 */
	public function getCategoryUrl($sLanguage = null)
	{
		return CatalogHelpers::getCategoryUrl($this->category_slug, $sLanguage);
	}

	/**
	 * Retourne les informations des fichiers d'un article
	 *
	 * @return array
	 */
	public function getFilesInfo()
	{
		$files = array();
		
		if (! $this->okt->catalog->config->files['enable'])
		{
			return $files;
		}
		
		$files_array = array_filter((array) unserialize($this->files));
		
		$j = 1;
		for ($i = 1; $i <= $this->okt->catalog->config->files['number']; $i ++)
		{
			if (! isset($files_array[$i]) || empty($files_array[$i]['filename']) || ! file_exists($this->okt->catalog->upload_dir . '/files/' . $files_array[$i]['filename']))
			{
				continue;
			}
			
			$mime_type = files::getMimeType($this->okt->catalog->upload_dir . '/files/' . $files_array[$i]['filename']);
			
			$files[$j] = array_merge(stat($this->okt->catalog->upload_dir . '/files/' . $files_array[$i]['filename']), array(
				'url' => $this->okt->catalog->upload_url . 'files/' . $files_array[$i]['filename'],
				'filename' => $files_array[$i]['filename'],
				'title' => $files_array[$i]['title'],
				'mime' => $mime_type,
				'type' => Utilities::getMediaType($mime_type),
				'ext' => pathinfo($this->okt->catalog->upload_dir . '/files/' . $files_array[$i]['filename'], PATHINFO_EXTENSION)
			));
			
			$j ++;
		}
		
		return $files;
	}

	/**
	 * Retourne les informations des images d'un article en fonction des données de la BDD
	 *
	 * @return array
	 */
	public function getImagesInfo()
	{
		if (! $this->okt->catalog->config->images['enable'])
		{
			return array();
		}
		
		return $this->getImagesArray();
	}

	/**
	 * Retourne les informations de la première image d'un article
	 * en fonction des données de la BDD
	 *
	 * @return array
	 */
	public function getFirstImageInfo()
	{
		if (! $this->okt->catalog->config->images['enable'])
		{
			return array();
		}
		
		$a = $this->getImagesArray();
		
		return isset($a[1]) ? $a[1] : array();
	}

	public function getImagesArray()
	{
		return array_filter((array) unserialize($this->images));
	}

	public function getCurrentImagesDir()
	{
		return $this->okt->catalog->upload_dir . '/img/' . $this->id;
	}

	public function getCurrentImagesUrl()
	{
		return $this->okt->catalog->upload_url . '/img/' . $this->id;
	}
}
