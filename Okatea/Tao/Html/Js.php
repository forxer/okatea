<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Html;

use Okatea\Tao\Html\Escaper;

/**
 * Permet de gérer les piles pour le Javascript et de retourner le HTML résultant.
 */
class Js
{
	/**
	 * Pile de fichiers JS
	 *
	 * @var array
	 */
	protected $aFilesStack = [];

	/**
	 * Pile de fichiers JS en Comentaires Conditionnels
	 *
	 * @var array
	 */
	protected $aCCFilesStack = [];

	/**
	 * Pile des conditions des Comentaires Conditionnels
	 *
	 * @var array
	 */
	protected $aCCCondStack = [];

	/**
	 * Pile de code JS
	 *
	 * @var array
	 */
	protected $aScriptStack = [];

	/**
	 * Pile de code JS de début
	 *
	 * @var array
	 */
	protected $aScriptStartStack = [];

	/**
	 * Pile de code JS "on ready"
	 *
	 * @var array
	 */
	protected $aReadyStack = [];

	/**
	 * La partie à afficher (traditionnellement 'admin' ou 'public')
	 *
	 * @var string
	 */
	protected $sPart = null;

	/**
	 * Constructeur.
	 *
	 * @return void
	 */
	public function __construct($sPart = null)
	{
		$this->sPart = $sPart;
	}

	/**
	 * Retourne tous le javascript des les différentes piles.
	 *
	 * @return string
	 */
	public function getJs()
	{
		return $this->getScriptStart() . "\n\n" . $this->getHtmlFiles() . "\n\n" . $this->getHtmlCCFiles() . "\n\n" . $this->getScript() . "\n\n" . $this->getReady();
	}

	public function __toString()
	{
		return $this->getJs();
	}

	/* Pile de fichiers
	----------------------------------------------------------*/

	/**
	 * Ajoute un fichier à la pile des fichiers JS.
	 *
	 * @param string $sSrc
	 * @param boolean $bToBegin
	 * @return void
	 */
	public function addFile($sSrc, $bToBegin = false)
	{
		if ($bToBegin) {
			array_unshift($this->aFilesStack, $sSrc);
		}
		else {
			$this->aFilesStack[] = $sSrc;
		}
	}

	/**
	 * Retourne la pile des fichiers JS.
	 *
	 * @return array
	 */
	public function getFilesStack()
	{
		$this->aFilesStack = array_unique($this->aFilesStack);

		return (!empty($this->aFilesStack) ? $this->aFilesStack : false);
	}

	/**
	 * Retourne la pile concaténée des fichiers JS.
	 *
	 * @return string
	 */
	public function getHtmlFiles()
	{
		if (($aFiles = $this->getFilesStack()) === false) {
			return false;
		}

		$sHtml = '';
		foreach ($aFiles as $sFile) {
			$sHtml .= self::formatFile($sFile);
		}

		return $sHtml;
	}

	/* Pile de fichiers CC
	----------------------------------------------------------*/

	/**
	 * Ajoute un fichier CC à la pile des fichiers JS.
	 *
	 * @param $src string
	 * @return void
	 */
	public function addCCFile($src, $condition = 'IE')
	{
		$this->aCCFilesStack[] = $src;
		$this->aCCCondStack[] = $condition;
	}

	/**
	 * Retourne la pile des fichiers CC JS.
	 *
	 * @return array
	 */
	public function getCCFilesStack()
	{
		$this->aCCFilesStack = array_unique($this->aCCFilesStack);

		return (!empty($this->aCCFilesStack) ? $this->aCCFilesStack : false);
	}

	/**
	 * Retourne la pile concaténée des fichiers CC JS.
	 *
	 * @return string
	 */
	public function getHtmlCCFiles()
	{
		if (($files = $this->getCCFilesStack()) === false) {
			return false;
		}

		$str = '';
		foreach ($files as $i => $file) {
			$str .= self::formatCCFile($file, $this->aCCCondStack[$i]);
		}

		return $str;
	}

	/* Pile de script de début
	----------------------------------------------------------*/

	/**
	 * Ajoute du code javascript en ligne de début à la pile.
	 *
	 * @param string $sJsCode
	 * @param boolean $bToBegin
	 * @return void
	 */
	public function addScriptStart($sJsCode, $bToBegin = false)
	{
		if ($bToBegin) {
			array_unshift($this->aScriptStartStack, $sJsCode);
		}
		else {
			$this->aScriptStartStack[] = $sJsCode;
		}
	}

	/**
	 * Retourne le javascript en ligne.
	 *
	 * @return string
	 */
	public function getScriptStart()
	{
		if (!empty($this->aScriptStartStack)) {
			return self::formatScript(implode("\n\n", $this->aScriptStartStack));
		}
	}

	/* Pile de script
	----------------------------------------------------------*/

	/**
	 * Ajoute du code javascript en ligne à la pile.
	 *
	 * @param string $sJsCode
	 * @param boolean $bToBegin
	 * @return void
	 */
	public function addScript($sJsCode, $bToBegin = false)
	{
		if ($bToBegin) {
			array_unshift($this->aScriptStack, $sJsCode);
		}
		else {
			$this->aScriptStack[] = $sJsCode;
		}
	}

	/**
	 * Retourne le javascript en ligne.
	 *
	 * @return string
	 */
	public function getScript()
	{
		if (!empty($this->aScriptStack)) {
			return self::formatScript(implode("\n\n", $this->aScriptStack));
		}
	}

	/* Pile de script à exécuter lorsque la page est chargée
	----------------------------------------------------------*/

	/**
	 * Ajoute du code javascript au "on ready" de jQuery.
	 *
	 * @param string $sJsCode
	 * @param boolean $bToBegin
	 * @return void
	 */
	public function addReady($sJsCode, $bToBegin = false)
	{
		if ($bToBegin) {
			array_unshift($this->aReadyStack, $sJsCode);
		}
		else {
			$this->aReadyStack[] = $sJsCode;
		}
	}

	/**
	 * Retourne le javascript "on ready"
	 *
	 * @return string
	 */
	public function getReady()
	{
		if (!empty($this->aReadyStack)) {
			return self::formatReady(implode("\n\n", $this->aReadyStack));
		}
	}

	/* Formatage du javascript pour le HTML
	----------------------------------------------------------*/

	/**
	 * Retourne le HTML de l'en-tête pour ajouter un fichier javascript
	 *
	 * @param string $src L'URL du fichier javascript
	 * @param string $format format de la chaine
	 * @return string
	 */
	public static function formatFile($src, $format = "<script type=\"text/javascript\" src=\"%s\"></script>\n")
	{
		return sprintf($format, $src);
	}

	/**
	 * Retourne le HTML de l'en-tête pour ajouter un fichier
	 * javascript en comentaire conditionnel
	 *
	 * @param string $src L'URL du fichier javascript
	 * @param string $condition La condition
	 * @param string $format format de la chaine
	 * @return string
	 */
	public static function formatCCFile($src, $condition, $format = "<script type=\"text/javascript\" src=\"%s\"></script>\n")
	{
		return Page::formatCC(self::formatFile($src, $format), $condition);
	}

	/**
	 * Retourne le HTML de l'en-tête pour ajouter du javascript
	 *
	 * @param string $js
	 *        	Le javascript
	 * @return string
	 */
	public static function formatScript($js)
	{
		return '<script type="text/javascript">' . PHP_EOL . '//<![CDATA[' . PHP_EOL . $js . PHP_EOL . '//]]>' . PHP_EOL . '</script>';
	}

	/**
	 * Retourne le HTML de l'en-tête pour ajouter
	 * du javascript lors de l'évènement "document.onload"
	 *
	 * @param string $js Le javascript
	 * @return string
	 */
	public static function formatReady($js)
	{
		return self::formatScript('jQuery(document).ready(function(){' . PHP_EOL . $js . PHP_EOL . '});');
	}

	/**
	 * Retourne le javascrip d'affection d'une variable.
	 *
	 * @param string $n
	 *        	nom de la variable
	 * @param string $v
	 *        	valeur de la variable
	 * @return string
	 */
	public static function variable($n, $v)
	{
		return $n . " = '" . Escaper::js($v) . "';\n";
	}
}
