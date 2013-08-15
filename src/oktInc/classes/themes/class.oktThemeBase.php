<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @class oktThemeBase
 * @ingroup okt_classes_themes
 * @brief Classe de base pour les thèmes
 *
 */

class oktThemeBase
{
	public $url;
	public $path;

	protected $okt;
	protected $aLessVariables = array();
	protected $aRubriques = array();

	/**
	 * Constructor.
	 *
	 * @param oktCore $okt
	 * @return void
	 */
	public function __construct($okt)
	{
		$this->okt = $okt;

		$this->url = OKT_THEME;
		$this->path = OKT_THEME_PATH;

		$this->setLessVariables(array(
			'common_url' 	=> "'".OKT_COMMON_URL."'",
			'theme_url' 	=> "'".OKT_THEME."'"
		));

		# -- CORE TRIGGER : themeInit
		$okt->triggers->callTrigger('themeInit', $okt, $this);

		if (method_exists($this, 'prepend')) {
			$this->prepend();
		}
	}


	/* Gestion des variables LESS
	----------------------------------------------------------*/

	/**
	 * Définit une liste de variables LESS.
	 *
	 * @param array $aVars
	 * @return void
	 */
	public function setLessVariables($aVars=array())
	{
		$this->aLessVariables = array_merge($this->aLessVariables, $aVars);
	}

	/**
	 * Définit une variable LESS.
	 *
	 * @param string $sKey
	 * @param string $sValue
	 * @return void
	 */
	public function setLessVariable($sKey, $sValue)
	{
		$this->aLessVariables[$sKey] = $sValue;
	}

	/**
	 * Retourne la liste des variables LESS.
	 *
	 * @return array
	 */
	public function getLessVariables()
	{
		return $this->aLessVariables;
	}

	/**
	 * Retourne une variable LESS donnée.
	 *
	 * @return array
	 */
	public function getLessVariable($sKey)
	{
		if (isset($this->aLessVariables[$sKey])) {
			return $this->aLessVariables[$sKey];
		}

		return null;
	}


	/* Gestion des rubriques
	----------------------------------------------------------*/

	/**
	 * Définit la liste de rubriques du site.
	 *
	 * @param array $aRubriques
	 * @return void
	 */
	public function setRubriques($aRubriques=array())
	{
		$this->aRubriques = array_merge($this->aRubriques, $aRubriques);
	}

	/**
	 * Définit une rubriques du site.
	 *
	 * @param string $sTitle
	 * @param string $sUrl
	 * @return void
	 */
	public function setRubrique($sTitle, $sUrl)
	{
		$this->aRubriques[$sTitle] = $sUrl;
	}

	/**
	 * Retourne la liste de rubriques du site.
	 *
	 * @return array
	 */
	public function getRubriques()
	{
		return $this->aRubriques;
	}

	/**
	 * Retourne une partie de la liste de rubriques du site.
	 *
	 * @param integer $iOffset Position de
	 * @param integer $iLength
	 * @return array
	 * @see http://www.php.net/manual/fr/function.array-slice.php
	 */
	public function getRubriquesRange($iOffset, $iLength=null)
	{
		return array_slice($this->aRubriques, $iOffset, $iLength, true);
	}

} # class
