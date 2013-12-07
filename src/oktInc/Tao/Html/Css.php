<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Html;

/**
 * Permet de gérer les piles pour les CSS et de retourner le HTML résultant.
 *
 */
class Css
{
	/**
	 * Pile de fichiers CSS
	 * @var array
	 */
	protected $aFilesStack = array();

	/**
	 * Pile de fichiers LESS CSS
	 * @var array
	 */
	protected $aLessFilesStack = array();

	/**
	 * Pile de fichiers CSS en Comentaires Conditionnels
	 * @var array
	 */
	protected $aCCFilesStack = array();

	/**
	 * Pile des conditions des Comentaires Conditionnels
	 * @var array
	 */
	protected $aCCCondStack = array();

	/**
	 * Pile de code CSS
	 * @var array
	 */
	protected $aCssStack = array();

	/**
	 * La partie à afficher (traditionnellement 'admin' ou 'public')
	 * @var string
	 */
	protected $sPart = null;


	/**
	 * Constructeur.
	 *
	 * @return void
	 */
	public function __construct($sPart=null)
	{
		$this->sPart = $sPart;
	}

	/**
	 * Retourne tous le javascript des les différentes piles.
	 *
	 * @return string
	 */
	public function getCss()
	{
		return
			$this->getHtmlFiles()."\n\n".
			$this->getHtmlLessFiles()."\n\n".
			$this->getHtmlCCFiles()."\n\n".
			$this->getInlineCss();
	}

	public function __toString()
	{
		return $this->getCss();
	}


	/* Pile de fichiers
	----------------------------------------------------------*/

	/**
	 * Ajoute un fichier à la pile des fichiers CSS
	 *
	 * @param $src string
	 * @param $media string
	 * @return void
	 */
	public function addFile($src, $media='screen')
	{
		$this->aFilesStack[] = $src.'|'.$media;
	}

	/**
	 * Retourne la pile des fichiers CSS.
	 *
	 * @return array
	 */
	public function getFilesStack()
	{
		$this->aFilesStack = array_unique($this->aFilesStack);

		return (!empty($this->aFilesStack) ? $this->aFilesStack : false);
	}

	/**
	 * Retourne la pile concaténée des fichiers CSS.
	 *
	 * @return string
	 */
	public function getHtmlFiles()
	{
		if (($aFiles = $this->getFilesStack()) === false) {
			return false;
		}

		$sHtml = '';
		foreach ($aFiles as $sFileInfo)
		{
			list($sFile, $sMedia) = explode('|', $sFileInfo);
			$sHtml .= self::formatHtmlCssFile($sFile, $sMedia);
		}

		return $sHtml;
	}


	/* Pile de fichiers LESS
	----------------------------------------------------------*/

	/**
	 * Ajoute un fichier à la pile des fichiers LESS CSS
	 *
	 * @param $src string
	 * @return void
	 */
	public function addLessFile($src)
	{
		$this->aLessFilesStack[] = $src;
	}

	/**
	 * Retourne la pile des fichiers LESS CSS.
	 *
	 * @return array
	 */
	public function getLessFilesStack()
	{
		$this->aLessFilesStack = array_unique($this->aLessFilesStack);

		return (!empty($this->aLessFilesStack) ? $this->aLessFilesStack : false);
	}

	/**
	 * Retourne la pile concaténée des fichiers LESS CSS.
	 *
	 * @return string
	 */
	public function getHtmlLessFiles()
	{
		if (($aFiles = $this->getLessFilesStack()) === false) {
			return false;
		}

		$sHtml = '';
		foreach ($aFiles as $sFile)
		{
			$sHtml .= self::formatHtmlCssFile($this->autoCompileLess($sFile));
		}

		return $sHtml;
	}

	protected function autoCompileLess($inputFile)
	{
		global $okt;

		$outputFile = OKT_PUBLIC_PATH.'/cache/'.md5($inputFile).'.css';
		$cacheFile = $outputFile.'.cache';

		if (file_exists($cacheFile)) {
			$cache = unserialize(file_get_contents($cacheFile));
		} else {
			$cache = $inputFile;
		}

		try
		{
			$less = new \lessc;

			$less->setPreserveComments(true);

			$less->setImportDir(array(
				(isset($okt->theme->path) ? $okt->theme->path.'/css/' : null),
				OKT_PUBLIC_PATH.'/css/less/'
			));

			$less->setVariables($okt->theme->getLessVariables());

			$newCache = $less->cachedCompile($cache);
		}
		catch (Exception $e) {
			$okt->error->set('lessphp fatal error: '.$e->getMessage());
			oktErrors::fatalScreen($e->getMessage());
		}


		if (!is_array($cache) || $newCache['updated'] > $cache['updated'])
		{
			file_put_contents($cacheFile, serialize($newCache));
			file_put_contents($outputFile, $newCache['compiled']);
		}

		return str_replace(OKT_PUBLIC_PATH, $okt->config->app_path.OKT_PUBLIC_DIR, $outputFile);
	}


	/* Pile de fichiers CC
	----------------------------------------------------------*/

	/**
	 * Ajoute un fichier CC à la pile des fichiers CSS.
	 *
	 * @param $src string
	 * @return void
	 */
	public function addCCFile($src,$condition='IE')
	{
		$this->aCCFilesStack[] = $src;
		$this->aCCCondStack[] = $condition;
	}

	/**
	 * Retourne la pile des fichiers CC CSS.
	 *
	 * @return array
	 */
	public function getCCFilesStack()
	{
		$this->aCCFilesStack = array_unique($this->aCCFilesStack);

		return (!empty($this->aCCFilesStack) ? $this->aCCFilesStack : false);
	}

	/**
	 * Retourne la pile concaténée des fichiers CC CSS.
	 *
	 * @return string
	 */
	public function getHtmlCCFiles()
	{
		if (($files = $this->getCCFilesStack()) === false) {
			return false;
		}

		$str = '';
		foreach ($files as $i=>$file) {
			$str .= self::formatCCFile($file,$this->aCCCondStack[$i]);
		}

		return $str;
	}


	/* Pile de code en ligne
	----------------------------------------------------------*/

	/**
	 * Ajoute du CSS en ligne à la pile.
	 *
	 * @param $str
	 * @return void
	 */
	public function addCSS($str)
	{
		$this->aCssStack[] = $str;
	}

	/**
	 * Retourne le CSS en ligne.
	 *
	 * @return string
	 */
	public function getInlineCss()
	{
		if (!empty($this->aCssStack)) {
			return self::formatCss(implode("\n\n",$this->aCssStack));
		}
	}


	/* Formatage du CSS pour le HTML
	----------------------------------------------------------*/

	/**
	 * Formate et retourne un lien CSS d'entête HTML
	 *
	 * @param string $src 		l'URL du fichier
	 * @param string $media 	L'attribut media (screen)
	 * @param string $rel 		L'attribut rel (stylesheet)
	 * @param string $format 	La chaîne de format ('<link type="text/css" href="%s" rel="%s" media="%s" />')
	 * @return string
	 */
	public static function formatHtmlCssFile($src,$media='screen',$rel='stylesheet',
		$format="<link type=\"text/css\" href=\"%s\" rel=\"%s\" media=\"%s\" />\n")
	{
		return sprintf($format,$src,$rel,$media);
	}

	/**
	 * Retourne le HTML de l'en-tête pour ajouter un fichier
	 * CSS en comentaire conditionnel
	 *
	 * @param string $src 		L'URL du fichier javascript
	 * @param string $condition La condition
	 * @param string $media 	L'attribut media (screen)
	 * @param string $rel 		L'attribut rel (stylesheet)
	 * @param string $format	Le format de la chaine ('<link type="text/css" href="%s" rel="%s" media="%s" />')
	 * @return string
	 */
	public static function formatCCFile($src,$condition,$media='screen',$rel='stylesheet',
		$format="<link type=\"text/css\" href=\"%s\" rel=\"%s\" media=\"%s\" />\n")
	{
		return Page::formatCC(self::formatHtmlCssFile($src,$media,$rel,$format),$condition);
	}

	/**
	 * Retourne le HTML de l'en-tête pour ajouter du CSS en ligne
	 *
	 * @param string $css 		Le code CSS
	 * @return string
	 */
	public static function formatCss($css)
	{
		return
		'<style type="text/css">'.PHP_EOL.
		'/* <![CDATA[ */'.PHP_EOL.
		$css.PHP_EOL.
		'/* ]]> */'.PHP_EOL.
		'</style>';
	}

} # class
