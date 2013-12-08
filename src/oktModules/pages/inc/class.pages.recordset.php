<?php
/**
 * @ingroup okt_module_pages
 * @brief Extension du recordset pour les pages
 *
 */

use Tao\Misc\Utilities as util;
use Tao\Database\Recordset;

class pagesRecordset extends Recordset
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
	 * Returns whether page is editable.
	 *
	 * @return boolean
	 */
	public function isEditable()
	{
		if ($this->okt->checkPerm('pages')) {
			return true;
		}

		return false;
	}

	/**
	 * Returns whether page is deletable
	 *
	 * @return boolean
	 */
	public function isDeletable()
	{
		if ($this->okt->checkPerm('pages_remove')) {
			return true;
		}

		return false;
	}

	/**
	 * Returns whether page is readable
	 *
	 * @return boolean
	 */
	public function isReadable()
	{
		static $perms = array();

		# si on as un "cache" on l'utilisent
		if (isset($perms[$this->id])) {
			return $perms[$this->id];
		}

		# si les permissions sont désactivées alors on as le droit
		if (!$this->okt->pages->canUsePerms())
		{
			$perms[$this->id] = true;
			return true;
		}

		# si on est superadmin on as droit à tout
		if ($this->okt->user->is_superadmin)
		{
			$perms[$this->id] = true;
			return true;
		}

		# récupération des permissions de la page
		$aPerms = $this->okt->pages->getPagePermissions($this->id);

		# si on a le groupe id 0 (zero) alors tous le monde a droit
		# sinon il faut etre dans le bon groupe
		if (in_array(0,$aPerms) || in_array($this->okt->user->group_id,$aPerms))
		{
			$perms[$this->id] = true;
			return true;
		}

		# toutes éventualités testées, on as pas le droit
		$perms[$this->id] = false;
		return false;
	}

	/**
	 * Retourne l'URL publique d'une page
	 *
	 * @param string $sLanguage
	 * @return string
	 */
	public function getPageUrl($sLanguage=null)
	{
		return pagesHelpers::getPageUrl($this->slug, $sLanguage);
	}

	/**
	 * Retourne l'URL publique d'une rubrique
	 *
	 * @param string $sLanguage
	 * @return string
	 */
	public function getCategoryUrl($sLanguage=null)
	{
		return pagesHelpers::getCategoryUrl($this->category_slug, $sLanguage);
	}

	/**
	 * Retourne les informations des fichiers d'une page
	 *
	 * @return array
	 */
	public function getFilesInfo()
	{
		$files = array();

		if (!$this->okt->pages->config->files['enable']) {
			return $files;
		}

		$files_array = array_filter((array)unserialize($this->files));

		$j=1;
		for ($i=1; $i<=$this->okt->pages->config->files['number']; $i++)
		{
			if (!isset($files_array[$i]) || empty($files_array[$i]['filename'])
				|| !file_exists($this->okt->pages->upload_dir.'files/'.$files_array[$i]['filename']))
			{
				continue;
			}

			$mime_type = files::getMimeType($this->okt->pages->upload_dir.'files/'.$files_array[$i]['filename']);

			$files[$j++] = array_merge(
				stat($this->okt->pages->upload_dir.'files/'.$files_array[$i]['filename']),
				array(
					'url' => $this->okt->pages->upload_url.'files/'.$files_array[$i]['filename'],
					'filename' => $files_array[$i]['filename'],
					'title' => $files_array[$i]['title'],
					'mime' => $mime_type,
					'type' => util::getMediaType($mime_type),
					'ext' => pathinfo($this->okt->pages->upload_dir.'files/'.$files_array[$i]['filename'],PATHINFO_EXTENSION)
				)
			);
		}

		return $files;
	}

	/**
	 * Retourne les informations des images d'une page en fonction des données de la BDD
	 *
	 * @return 	array
	 */
	public function getImagesInfo()
	{
		if (!$this->okt->pages->config->images['enable']) {
			return array();
		}

		return $this->getImagesArray();
	}

	/**
	 * Retourne les informations de la première image d'une page
	 * en fonction des données de la BDD
	 *
	 * @return 	array
	 */
	public function getFirstImageInfo()
	{
		if (!$this->okt->pages->config->images['enable']) {
			return array();
		}

		$a = $this->getImagesArray();

		return isset($a[1]) ? $a[1] : array();
	}

	public function getImagesArray()
	{
		return is_array($this->images) ? $this->images : array_filter((array)unserialize($this->images));
	}

	public function getCurrentImagesDir()
	{
		return $this->okt->pages->upload_dir.'img/'.$this->id.'/';
	}

	public function getCurrentImagesUrl()
	{
		return $this->okt->pages->upload_url.'img/'.$this->id.'/';
	}

} # class
