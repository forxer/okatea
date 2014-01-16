<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Themes\Editor;

use Okatea\Tao\Themes\Collection;
use Okatea\Tao\Themes\Editor\Iterator\ThemeFiles;
use Okatea\Tao\Themes\Editor\Iterator\ThemeDirsForSelect;

/**
 * Classe de l'éditeur de thème.
 *
 */
class Editor
{
	/**
	 * Référence du core.
	 * @var oktCore
	 */
	protected $okt;

	/**
	 * Instance du gestionnaire de thèmes.
	 * @var Okatea\Tao\Themes\Collection
	 */
	protected $oThemes;

	/**
	 * Liste des thèmes
	 * @var array
	 */
	protected $aThemes;

	/**
	 * Le nom du répertoire des thèmes.
	 * @var string
	 */
	protected $sThemesDir;

	/**
	 * Le chemin du répertoire des thèmes.
	 * @var string
	 */
	protected $sThemesPath;


	/**
	 * L'identifiant du thème en cours d'édition.
	 * @var string
	 */
	protected $sThemeId = null;

	/**
	 * Les infos du thème en cours d'édition.
	 * @var array
	 */
	protected $aThemeInfos = array();

	/**
	 * Le chemin du thème en cours d'édition.
	 * @var string
	 */
	protected $sThemePath = null;

	/**
	 * L'URL du thème en cours d'édition.
	 * @var string
	 */
	protected $sThemeUrl = null;

	/**
	 * La liste des fichiers du thème en cours d'édition.
	 * @var ThemeFilesIterator
	 */
	protected $oThemeFiles = null;

	/**
	 * Le nom du fichier en cours d'édition.
	 * @var string
	 */
	protected $sFilename = null;

	/**
	 * Les infos du fichier en cours d'édition.
	 * @var \SplFileInfo
	 */
	protected $oFileInfos = null;

	/**
	 * L'extension du fichier en cours d'édition.
	 * @var string
	 */
	protected $sFileExtension = null;

	/**
	 * La liste des fichiers backup du fichier en cours d'édition.
	 * @var array
	 */
	protected $aBackupFiles = array();


	/**
	 * Constructor.
	 *
	 * @param oktCore $okt
	 * @param string $sThemesPath
	 * @return void
	 */
	public function __construct($okt, $sThemesPath)
	{
		$this->okt = $okt;

		$this->sThemesPath = $sThemesPath;
		$this->sThemesDir = basename($sThemesPath);

		$this->oThemes = new Collection($okt, $sThemesPath);
		$this->aThemes = $this->oThemes->getThemesAdminList();
	}


	/* Méthodes pour l'édition d'un thème
	----------------------------------------------------------*/

	/**
	 * Chargement d'un thème donné dans l'éditeur.
	 *
	 * @param string $sThemeId
	 * @return void
	 */
	public function loadTheme($sThemeId)
	{
		if (!isset($this->aThemes[$sThemeId])) {
			throw new Exception(sprintf(__('c_a_te_error_theme_%s_not_exists'), $sThemeId));
		}

		$this->sThemeId = $sThemeId;

		$this->aThemeInfos = $this->aThemes[$this->sThemeId];

		$this->sThemePath = $this->sThemesPath.'/'.$this->sThemeId;
		$this->sThemeUrl = $this->okt->config->app_path.$this->sThemesDir.'/'.$this->sThemeId;
	}

	/**
	 * Retourne la liste des thèmes.
	 *
	 * @return array
	 */
	public function getThemes()
	{
		return $this->aThemes;
	}

	/**
	 * Retourne les infos du thème en cours d'édition.
	 *
	 * @return array
	 */
	public function getThemeInfos()
	{
		return $this->aThemeInfos;
	}

	/**
	 * Retourne une info donnée du thème en cours d'édition.
	 *
	 * @param string $sName
	 * return string
	 */
	public function getThemeInfo($sName)
	{
		if (isset($this->aThemeInfos[$sName])) {
			return $this->aThemeInfos[$sName];
		}
	}

	/**
	 * Charge la liste des fichiers du thème en cours d'édition.
	 *
	 * @return void
	 */
	public function loadThemeFilesTree()
	{
		if ($this->sThemeId) {
			$this->oThemeFiles = new ThemeFiles(new \RecursiveDirectoryIterator($this->sThemePath), \RecursiveIteratorIterator::SELF_FIRST);
		}
	}

	/**
	 * Charge la liste des dossiers du thème en cours d'édition.
	 *
	 * @return void
	 */
	public function loadThemeDirTree()
	{
		if ($this->sThemeId) {
			$this->oThemeFiles = new ThemeDirsForSelect(new \RecursiveDirectoryIterator($this->sThemePath), \RecursiveIteratorIterator::SELF_FIRST);
		}
	}

	/**
	 * Retourne la liste des templates des thèmes.
	 */
	public function getTemplatesDirs()
	{
		$this->aTemplatesPath = array();
		foreach ($this->aThemes as $aTheme)
		{
	//		$this->aTemplatesPath[$aTheme['name']] = array();

			$i = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->sThemesPath.'/'.$aTheme['id'].'/templates'), \RecursiveIteratorIterator::SELF_FIRST);

			foreach ($i as $f)
			{
				if ($f->isFile()
						&& $f->getExtension() == 'php'
		//				&& $f->getFilename() != 'layout.php'
						&& $f->getFilename() != '_define.php')
				{
					$this->aTemplatesPath[$aTheme['name']][str_replace($this->sThemesPath.'/'.$aTheme['id'].'/templates', '', $f->getPathname())] = str_replace($this->sThemesPath, '', $f->getPathname());
				}
			}
		}

		return $this->aTemplatesPath;
	}

	/**
	 * Retourne la liste des fichiers du thème en cours d'édition.
	 *
	 * @return ThemeFilesIterator
	 */
	public function getThemeFiles()
	{
		return $this->oThemeFiles;
	}

	/**
	 * Retourne le chemin du thème en cours d'édition.
	 *
	 * @return string
	 */
	public function getThemePath()
	{
		return $this->sThemePath;
	}

	/**
	 * Retourne l'URL du thème en cours d'édition.
	 *
	 * @return string
	 */
	public function getThemeUrl()
	{
		return $this->sThemeUrl;
	}


	/* Méthodes pour l'édition d'un fichier
	----------------------------------------------------------*/

	/**
	 * Chargement d'un fichier donné dans l'éditeur.
	 *
	 * @param string $sThemeId
	 * @return void
	 */
	public function loadFile($sFilename)
	{
		if (!file_exists($this->sThemePath.$sFilename)) {
			throw new Exception(sprintf(__('c_a_te_error_file_%s_not_exists'), $sFilename));
		}

		$this->sFilename = $sFilename;

		$this->oFileInfos = new \SplFileInfo($this->sThemePath.$this->sFilename);

		$this->sFileExtension = $this->oFileInfos->getExtension();

		$this->loadBackupFiles();
	}

	/**
	 * Retourne le nom du fichier en cours d'édition.
	 *
	 * @return string
	 */
	public function getFilename()
	{
		return $this->sFilename;
	}

	/**
	 * Retourne les infos du fichier en cours d'édition.
	 *
	 * @return \SplFileInfo
	 */
	public function getFileInfos()
	{
		return $this->oFileInfos;
	}

	/**
	 * Retourne l'extension du fichier en cours d'édition.
	 *
	 * @return array
	 */
	public function getFileExtension()
	{
		return $this->sFileExtension;
	}

	/**
	 * Charge la liste des fichiers backup du fichier en cours d'édition.
	 *
	 * @return void
	 */
	public function loadBackupFiles()
	{
		$sBasename = str_replace('.'.$this->sFileExtension, '', $this->sFilename);
		$sPatern = $sBasename.'_????-??-??-??-??-??.'.$this->sFileExtension.'.bak';

		$this->aBackupFiles = glob($this->sThemePath.$sPatern);

		foreach ($this->aBackupFiles as $k=>$sFile) {
			$this->aBackupFiles[$k] = str_replace($this->sThemePath, '', $sFile);
		}
	}

	/**
	 * Retourne la liste des fichiers backup du fichier en cours d'édition.
	 *
	 *  @return array
	 */
	public function getBackupFiles()
	{
		return $this->aBackupFiles;
	}

	/**
	 * Enregistre un nouveau contenu pour le fichier en cours d'édition.
	 *
	 * @param string $sContent
	 * @param boolean $bMPakeBakcup
	 */
	public function saveFile($sContent, $bMPakeBakcup)
	{
		if ($bMPakeBakcup) {
			$this->makeBackup();
		}

		return file_put_contents($this->sThemePath.$this->sFilename, $sContent);
	}

	/**
	 * Création d'une copie de sauvegarde.
	 *
	 */
	public function makeBackup()
	{
		$sBackupFilename = str_replace(
			'.'.$this->sFileExtension,
			date('_Y-m-d-H-i-s').'.'.$this->sFileExtension.'.bak',
			$this->sFilename
		);

		copy($this->sThemePath.$this->sFilename, $this->sThemePath.$sBackupFilename);
	}

	/**
	 * Restauration d'une copie de sauvegarde.
	 *
	 * @param string $sBackupFile
	 */
	public function restoreBackupFile($sBackupFile)
	{
		if (!in_array($sBackupFile,$this->aBackupFiles)) {
			throw new Exception(sprintf(__('c_a_te_error_file_%s_not_exists'), $sBackupFile));
		}

		$this->makeBackup();

		file_put_contents($this->sThemePath.$this->sFilename, file_get_contents($this->sThemePath.$sBackupFile));

		$this->deleteBackupFile($sBackupFile);
	}

	public function deleteBackupFile($sBackupFile)
	{
		if (!in_array($sBackupFile,$this->aBackupFiles)) {
			throw new Exception(sprintf(__('c_a_te_error_file_%s_not_exists'), $sBackupFile));
		}

		unlink($this->sThemePath.$sBackupFile);
	}

	/**
	 * Retourne le mode pour CodeMirror en fonction de l'extension du fichier.
	 *
	 * @return string
	 */
	public function getCodeMirrorMode()
	{
		$sMode = null;

		if ($this->sFileExtension == 'php') {
			$sMode = 'application/x-httpd-php';
		}
		elseif ($this->sFileExtension == 'html') {
			$sMode = 'text/html';
		}
		elseif ($this->sFileExtension == 'js') {
			$sMode = 'text/javascript';
		}
		elseif ($this->sFileExtension == 'css') {
			$sMode = 'text/css';
		}
		elseif ($this->sFileExtension == 'less') {
			$sMode = 'text/x-less';
		}
		elseif ($this->sFileExtension == 'yml') {
			$sMode = 'text/x-yaml';
		}

		return $sMode;
	}
}
