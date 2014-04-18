<?php
/**
 * @ingroup okt_module_faq
 * @brief Extension du recordset pour les questions internationnalisées
 *
 */

use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Database\Recordset;

class FaqRecordset extends Recordset
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
	 * Returns whether post is readable
	 *
	 * @return boolean
	 */
	public function isReadable()
	{
		return true;
	}

	/**
	 * Retourne l'URL publique d'une question
	 *
	 * @return string
	 */
	public function getQuestionUrl($sLanguage=null)
	{
		return FaqHelpers::getQuestionUrl($this->slug, $sLanguage);
	}

	/**
	 * Retourne les informations des fichiers d'une question
	 *
	 * @param string $imageslist
	 * @return 	array
	 */
	function getFilesInfo()
	{
		$files_infos = array();
		$files = unserialize($this->files);

		foreach ($files as $locale=>$files)
		{
			$files_infos[$locale] = array();
			$i = 0;
			foreach ($files as $file)
			{
				$path = $this->okt->faq->upload_dir.'/'.$file;
				$url = $this->okt->faq->upload_url.'/'.$file;

				if (!file_exists($path)) {
					continue;
				}

				$mime_type = files::getMimeType($path);

				$files_infos[$locale][$i] = array_merge(
					stat($path),
					array(
						'filename' => $file,
						'path' => $path,
						'url' => $url,
						'mime' => $mime_type,
						'type' => Utilities::getMediaType($mime_type),
						'ext' => pathinfo($path,PATHINFO_EXTENSION)
					)
				);

				$i++;
			}
		}

		return $files_infos;
	}

	/**
	 * Retourne les informations des images d'une question en fonction des données de la BDD
	 *
	 * @return 	array
	 */
	public function getImagesInfo()
	{
		if (!$this->okt->faq->config->images['enable']) {
			return array();
		}

		return $this->getImagesArray();
	}

	/**
	 * Retourne les informations de la première image d'une question
	 * en fonction des données de la BDD
	 *
	 * @return 	array
	 */
	public function getFirstImageInfo()
	{
		if (!$this->okt->faq->config->images['enable']) {
			return array();
		}

		$a = $this->getImagesArray();

		return isset($a[1]) ? $a[1] : array();
	}

	public function getImagesArray()
	{
		return array_filter((array)unserialize($this->images));
	}

	public function getCurrentImagesDir()
	{
		return $this->okt->faq->upload_dir.'/img/'.$this->id;
	}

	public function getCurrentImagesUrl()
	{
		return $this->okt->faq->upload_url.'/img/'.$this->id;
	}


}
