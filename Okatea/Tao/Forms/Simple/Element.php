<?php

/**
 * @class oktFormElement
 * @ingroup okt_classes_form
 * @brief Form element.
 *
 */
namespace Okatea\Tao\Forms\Simple;

abstract class Element
{

	protected $aConfig = array(
		'html' => '<p><label for="{{id}}">{{label}}</label><input{{attributes}} value="{{value}}" /></p>',
		'label' => null,
		'value' => null
	);

	protected $aAttributes = [];

	protected $aSearchReplace = [];

	/**
	 * Constructor
	 *
	 * @param array $aConfig        	
	 * @param array $aAttributes        	
	 * @return void
	 */
	public function __construct($aConfig = [], $aAttributes = [])
	{
		$this->setConfig($aConfig);
		$this->setAttributes($aAttributes);
	}

	/**
	 * Définit les attributs de l'élément.
	 *
	 * @param array $aAttributes        	
	 * @return void
	 */
	public function setAttributes($aAttributes)
	{
		$this->aAttributes = $aAttributes + $this->aAttributes;
	}

	/**
	 * Définit un attribut.
	 *
	 * @param string $sName        	
	 * @param string $sValue        	
	 */
	public function setAttribute($sName, $sValue = null)
	{
		$this->aAttributes[$sName] = $sValue;
		
		return $this;
	}

	/**
	 * Retourne la liste des attributs.
	 *
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->aAttributes;
	}

	/**
	 * Retourne la valeur d'un attribut.
	 *
	 * @return array
	 */
	public function getAttribute($sName)
	{
		if (!isset($this->aAttributes[$sName]))
		{
			return null;
		}
		
		return $this->aAttributes[$sName];
	}

	/**
	 * Définit la configuration.
	 *
	 * @param array $aConfig        	
	 * @return void
	 */
	public function setConfig($aConfig)
	{
		$this->aConfig = $aConfig + $this->aConfig;
	}

	/**
	 * Définit une valeur de configuration.
	 *
	 * @param string $sName        	
	 * @param string $sValue        	
	 */
	public function setConfigValue($sName, $sValue = null)
	{
		$this->aConfig[$sName] = $sValue;
		
		return $this;
	}

	/**
	 * Prepend somthing.
	 *
	 * @return NULL
	 */
	public function prepend()
	{
		return null;
	}

	/**
	 * Append somthing.
	 *
	 * @return NULL
	 */
	public function append()
	{
		return null;
	}

	/**
	 * Réalise le rendu de l'élément.
	 *
	 * @return string
	 */
	public function render()
	{
		$this->setSearchReplace();
		
		return str_replace(array_keys($this->aSearchReplace), array_values($this->aSearchReplace), $this->aConfig['html']);
	}

	/**
	 * Définit la liste des termes et des valeurs de remplacement.
	 */
	protected function setSearchReplace()
	{
		$this->aSearchReplace = array(
			'{{label}}' => $this->aConfig['label'],
			'{{value}}' => $this->aConfig['value'],
			'{{attributes}}' => $this->renderAttributes()
		);
		
		foreach ($this->aAttributes as $sName => $sValue)
		{
			$this->aSearchReplace['{{' . $sName . '}}'] = $sValue;
		}
	}

	/**
	 * Réalise le rendu des attributs du champ.
	 *
	 * @return string
	 */
	protected function renderAttributes()
	{
		$aAttributes = [];
		
		foreach ($this->aAttributes as $sName => $sValue)
		{
			$aAttributes[] = ' ' . $sName . '="' . $sValue . '"';
		}
		
		return implode(' ', $aAttributes);
	}
}
