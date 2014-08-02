<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Extensions\Themes;

use Okatea\Tao\Extensions\Extension;

class Theme extends Extension
{

	public $url;

	protected $aLessVariables = array();

	final public function init()
	{
		parent::init();
		
		$this->public_path = $this->okt['public_dir'] . '/themes/' . $this->id();
		$this->public_url = $this->okt['public_url'] . '/themes/' . $this->id();
		
		$this->setLessVariables(array(
			'public_url' => "'" . $this->okt['public_url'] . "'",
			'theme_url' => "'" . $this->public_url . "'"
		));
	}

	final public function initNs($ns)
	{
		parent::initNs($ns);
	}

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
