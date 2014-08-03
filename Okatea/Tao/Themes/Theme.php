<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Themes;

/**
 * Classe de base pour les thèmes.
 */
class Theme
{

	public $url;

	public $path;

	/**
	 * Okatea application instance.
	 *
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	protected $aLessVariables = array();

	protected $aRubriques = array();

	/**
	 * Constructor.
	 *
	 * @param Okatea\Tao\Application $okt        	
	 * @return void
	 */
	public function __construct($okt)
	{
		$this->okt = $okt;
		
		$this->url = $this->okt['config']->app_path . basename($this->okt['themes_dir']) . '/' . $this->okt->theme_id;
		
		$this->path = $this->okt['themes_dir'] . '/' . $this->okt->theme_id;
		
		$this->setLessVariables(array(
			'public_url' => "'" . $this->okt['public_url'] . "'",
			'theme_url' => "'" . $this->url . "'"
		));
		
		# Chargement des éventuelles traductions personalisées
		$this->okt['l10n']->loadFile($this->path . '/Locales/%s/custom');
		
		# -- CORE TRIGGER : themeInit
		$okt['triggers']->callTrigger('themeInit');
		
		if (method_exists($this, 'prepend'))
		{
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
	public function setLessVariables($aVars = array())
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
		if (isset($this->aLessVariables[$sKey]))
		{
			return $this->aLessVariables[$sKey];
		}
		
		return null;
	}
}
