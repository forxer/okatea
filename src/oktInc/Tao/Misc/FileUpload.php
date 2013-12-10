<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Misc;

use Tao\Misc\Utilities as util;

/**
 * Outil pour l'upload de fichier.
 *
 */
class FileUpload
{
	/**
	 * Référence de l'objet core.
	 * @var object oktCore
	 */
	protected $okt;

	/**
	 * Référence de l'objet gestionnaire d'erreurs
	 * @var object oktError
	 */
	protected $error;

	/**
	 * La configuration de l'upload des fichiers
	 * @var array
	 */
	protected $config = null;

	/**
	 * Le chemin du répertoire des fichiers
	 * @var string
	 */
	protected $upload_dir;

	/**
	 * L'URL du répertoire des fichiers
	 * @var string
	 */
	protected $upload_url;

	public function __construct($okt, $config, $upload_dir, $upload_url)
	{
		$this->okt = $okt;
		$this->error = $okt->error;

		$this->config = array_merge($this->getDefaultConfig(), $config);
		$this->config['allowed_exts'] = explode(',',$this->config['allowed_exts']);
		$this->config['allowed_exts'] = array_map('trim',$this->config['allowed_exts']);

		$this->upload_dir = $upload_dir;
		$this->upload_url = $upload_url;
	}

	/**
	 * Retourne les données de configuration par défaut
	 *
	 * @return array
	 */
	private function getDefaultConfig()
	{
		return array(
			'number' => 5,
			'allowed_exts' => 'txt,pdf',
			'files_patern' => 'p_files_%s',
			'files_title_patern' => 'p_files_title_%s'
		);
	}

	/**
	 * Vérifie si l'extension est autorisée
	 *
	 * @param $sExtension string
	 * @return void
	 */
	private function checkFile($sExtension)
	{
		if (!in_array($sExtension,$this->config['allowed_exts'])) {
			throw new Exception('Type de fichier non-autorisé.');
		}
	}

	/**
	 * Ajout des fichiers
	 *
	 * @return array
	 */
	public function addFiles($iItemId)
	{
		$aFiles = array();

		$j = 1;

		for ($i=1; $i<=$this->config['number']; $i++)
		{
			$aFiles[$j] = '';

			if (!isset($_FILES[sprintf($this->config['files_patern'],$i)]) || empty($_FILES[sprintf($this->config['files_patern'],$i)]['tmp_name'])) {
				continue;
			}

			$sUploadedFile = $_FILES[sprintf($this->config['files_patern'],$i)];

			try {
				$sExtension = pathinfo($sUploadedFile['name'],PATHINFO_EXTENSION);

				# des erreurs d'upload ?
				util::uploadStatus($sUploadedFile);

				# vérification de l'extension
				$this->checkFile($sExtension);

				# création du répertoire s'il existe pas
				if (!file_exists($this->upload_dir)) {
					files::makeDir($this->upload_dir,true);
				}

				$sDestination = $this->upload_dir.$iItemId.'-'.$j.'.'.$sExtension;

				if (!move_uploaded_file($sUploadedFile['tmp_name'],$sDestination)) {
					throw new Exception('Impossible de déplacer sur le serveur le fichier téléchargé.');
				}

				$aFiles[$j] = array(
					'filename' => basename($sDestination),
					'title' => (!empty($_REQUEST[sprintf($this->config['files_title_patern'],$i)]) ? $_REQUEST[sprintf($this->config['files_title_patern'],$i)] : $j)
				);

				$j++;
			}
			catch (Exception $e) {
				$this->error->set('Problème avec le fichier '.$i.' : '.$e->getMessage());
			}
		}

		return array_filter($aFiles);
	}

	/**
	 * Modification des fichiers
	 *
	 * @return array
	 */
	public function updFiles($iItemId, $aCurrentFiles=array())
	{
		$aNewFiles = array();

		$j = 1;

		for ($i=1; $i<=$this->config['number']; $i++)
		{
			if (!isset($_FILES[sprintf($this->config['files_patern'],$i)]) || empty($_FILES[sprintf($this->config['files_patern'],$i)]['tmp_name']))
			{
				if (isset($aCurrentFiles[$i]))
				{
					$aNewFiles[$j] = array(
						'filename' => $aCurrentFiles[$i]['filename'],
						'title' => (!empty($_REQUEST[sprintf($this->config['files_title_patern'],$i)]) ? $_REQUEST[sprintf($this->config['files_title_patern'],$i)] : $aCurrentFiles[$i]['title'])
					);
					$j++;
				}

				continue;
			}

			$sUploadedFile = $_FILES[sprintf($this->config['files_patern'],$i)];

			try {
				$sExtension = pathinfo($sUploadedFile['name'],PATHINFO_EXTENSION);

				# des erreurs d'upload ?
				util::uploadStatus($sUploadedFile);

				# vérification de l'extension
				$this->checkFile($sExtension);

				# vérification du type
//				$aAllowedTypes = array('image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png');
//				if (!in_array($sUploadedFile['type'], $aAllowedTypes)) {
//					throw new Exception('Type de fichier non-autorisé.');
//				}

				# création du répertoire s'il existe pas
				if (!file_exists($this->upload_dir)) {
					files::makeDir($this->upload_dir,true);
				}

				# suppression de l'éventuel ancien fichier
				if (isset($aCurrentFiles[$i]) && files::isDeletable($this->upload_dir.$aCurrentFiles[$i])) {
					unlink($this->upload_dir.$aCurrentFiles[$i]);
				}

				$sDestination = $this->upload_dir.$iItemId.'-'.$j.'.'.$sExtension;

				if (!move_uploaded_file($sUploadedFile['tmp_name'],$sDestination)) {
					throw new Exception('Impossible de déplacer sur le serveur le fichier téléchargé.');
				}

				$aNewFiles[$j] = array(
					'filename' => basename($sDestination),
					'title' => (!empty($_REQUEST[sprintf($this->config['files_title_patern'],$i)]) ? $_REQUEST[sprintf($this->config['files_title_patern'],$i)] : $j)
				);

				$j++;
			}
			catch (Exception $e) {
				$this->okt->error->set('Problème avec le fichier '.$i.' : '.$e->getMessage());
			}
		}

		return array_filter($aNewFiles);
	}

	public function deleteAllFiles($aCurrentFiles)
	{
		foreach ($aCurrentFiles as $file_id=>$file)
		{
			if (isset($aCurrentFiles[$file_id]) && !empty($aCurrentFiles[$file_id]['filename'])
				&& file_exists($this->upload_dir.$aCurrentFiles[$file_id]['filename']))
			{
				unlink($this->upload_dir.$aCurrentFiles[$file_id]['filename']);
			}
		}
	}

	/**
	 * Suppression d'un fichier
	 *
	 * @param integer $iItemId
	 * @param array $aCurrentFiles
	 * @param integer $iFileId
	 * @return array
	 */
	public function deleteFile($iItemId, $aCurrentFiles, $iFileId)
	{
		if (!isset($aCurrentFiles[$iFileId]) || empty($aCurrentFiles[$iFileId]['filename'])) {
			$this->error->set('Le fichier n’existe pas.');
			return false;
		}

		# suppression des fichiers sur le disque
		if (file_exists($this->upload_dir.$aCurrentFiles[$iFileId]['filename'])) {
			unlink($this->upload_dir.$aCurrentFiles[$iFileId]['filename']);
		}

		# suppression du nom pour les infos de la BDD
		unset($aCurrentFiles[$iFileId]);

		$aNewFiles = array();

		$j = 1;
		for ($i=1; $i<=$this->config['number']; $i++)
		{
			if (!isset($aCurrentFiles[$i]) || empty($aCurrentFiles[$i]['filename'])) {
				continue;
			}

			$sExtension = pathinfo($aCurrentFiles[$i]['filename'],PATHINFO_EXTENSION);

			$sNewName = $iItemId.'-'.$j.'.'.$sExtension;

			if (file_exists($this->upload_dir.$aCurrentFiles[$i]['filename'])) {
				rename($this->upload_dir.$aCurrentFiles[$i]['filename'], $this->upload_dir.'/'.$sNewName);
			}

			$aNewFiles[$j] = array(
				'filename' => $sNewName,
				'title' => $aCurrentFiles[$i]['title']
			);

			$j++;
		}

		return array_filter($aNewFiles);
	}

}
