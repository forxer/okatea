<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Pages;

use Okatea\Tao\Misc\FileUpload;

class PagesFiles extends FileUpload
{

	protected $modulePages;

	protected $pages;

	public function __construct($okt)
	{
		parent::__construct(
			$okt,
			$okt->module('Pages')->config->files,
			$okt->module('Pages')->upload_dir . '/files',
			$okt->module('Pages')->upload_url . '/files'
		);

		$this->modulePages = $this->okt->module('Pages');
		$this->pages = $this->modulePages->pages;
	}

	/**
	 * Ajout de fichier(s) à une page donnée
	 *
	 * @param
	 *        	$iPageId
	 * @return boolean
	 */
	public function add($iPageId)
	{
		if (! $this->modulePages->config->files['enable'])
		{
			return null;
		}

		$aFiles = $this->addFiles($iPageId);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		return $this->pages->updPageFiles($iPageId, $aFiles);
	}

	/**
	 * Modification de fichier(s) d'une page donnée
	 *
	 * @param
	 *        	$iPageId
	 * @return boolean
	 */
	public function update($iPageId)
	{
		if (! $this->modulePages->config->files['enable'])
		{
			return null;
		}

		$aCurrentFiles = $this->pages->getPageFiles($iPageId);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		$aFiles = $this->updFiles($iPageId, $aCurrentFiles);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		return $this->pages->updPageFiles($iPageId, $aFiles);
	}

	/**
	 * Suppression d'un fichier donné d'une page donnée
	 *
	 * @param
	 *        	$iPageId
	 * @param
	 *        	$file_id
	 * @return boolean
	 */
	public function delete($iPageId, $file_id)
	{
		$aCurrentFiles = $this->pages->getPageFiles($iPageId);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		$aNewFiles = $this->deleteFile($iPageId, $aCurrentFiles, $file_id);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		return $this->pages->updPageFiles($iPageId, $aNewFiles);
	}

	/**
	 * Suppression des fichiers d'une page donnée
	 *
	 * @param
	 *        	$iPageId
	 * @return boolean
	 */
	public function deleteAll($iPageId)
	{
		$aCurrentFiles = $this->pages->getPageFiles($iPageId);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		$this->deleteAllFiles($aCurrentFiles);

		return $this->pages->updPageFiles($iPageId);
	}

}
