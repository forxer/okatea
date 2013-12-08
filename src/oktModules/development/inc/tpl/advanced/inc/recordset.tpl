<?php
##header##

use Tao\Misc\Utilities as util;
use Tao\Database\Recordset;

class ##module_camel_case_id##Recordset extends Recordset
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
	 * Retourne l'URL publique d'un élément
	 *
	 * @return string
	 */
	public function getItemUrl()
	{
		return $this->okt->page->getBaseUrl().$this->okt->##module_id##->config->public_item_url.'/'.$this->slug;
	}

	/**
	 * Retourne les informations des fichiers d'un article
	 *
	 * @return array
	 */
	public function getFilesInfo()
	{
		$files = array();

		if (!$this->okt->##module_id##->config->files['enable']) {
			return $files;
		}

		$files_array = array_filter((array)unserialize($this->files));

		$j=1;
		for ($i=1; $i<=$this->okt->##module_id##->config->files['number']; $i++)
		{
			if (!isset($files_array[$i]) || empty($files_array[$i]['filename'])
				|| !file_exists($this->okt->##module_id##->upload_dir.'files/'.$files_array[$i]['filename']))
			{
				continue;
			}

			$mime_type = files::getMimeType($this->okt->##module_id##->upload_dir.'files/'.$files_array[$i]['filename']);

			$files[$j] = array_merge(
				stat($this->okt->##module_id##->upload_dir.'files/'.$files_array[$i]['filename']),
				array(
					'url' => $this->okt->##module_id##->upload_url.'files/'.$files_array[$i]['filename'],
					'filename' => $files_array[$i]['filename'],
					'title' => $files_array[$i]['title'],
					'mime' => $mime_type,
					'type' => util::getMediaType($mime_type),
					'ext' => pathinfo($this->okt->##module_id##->upload_dir.'files/'.$files_array[$i]['filename'],PATHINFO_EXTENSION)
				)
			);

			$j++;
		}

		return $files;
	}

	/**
	 * Retourne les informations des images d'un article en fonction des données de la BDD
	 *
	 * @return 	array
	 */
	public function getImagesInfo()
	{
		if (!$this->okt->##module_id##->config->images['enable']) {
			return array();
		}

		return $this->getImagesArray();
	}

	/**
	 * Retourne les informations de la première image d'un article
	 * en fonction des données de la BDD
	 *
	 * @return 	array
	 */
	public function getFirstImageInfo()
	{
		if (!$this->okt->##module_id##->config->images['enable']) {
			return array();
		}

		$a = $this->getImagesArray();

		return isset($a[1]) ? $a[1] : array();
	}

	protected function getImagesArray()
	{
		return array_filter((array)unserialize($this->images));
	}

	protected function getCurrentImagesDir()
	{
		return $this->okt->##module_id##->upload_dir.'img/'.$this->id.'/';
	}

	protected function getCurrentImagesUrl()
	{
		return $this->okt->##module_id##->upload_url.'img/'.$this->id.'/';
	}

} # class
