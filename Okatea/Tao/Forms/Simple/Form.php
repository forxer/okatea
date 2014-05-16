<?php

/**
 * @class oktForm
 * @ingroup okt_classes_form
 * @brief Form builder.
 *
 */
namespace Okatea\Tao\Forms\Simple;

use Okatea\Tao\Forms\Simple\Element;
use Okatea\Tao\Forms\Simple\Elements\ExtraHtml;
use Okatea\Tao\Forms\Simple\Elements\InputText;
use Okatea\Tao\Forms\Simple\Elements\InputPassword;
use Okatea\Tao\Forms\Simple\Elements\InputHidden;
use Okatea\Tao\Forms\Simple\Elements\Textarea;

class Form
{

	protected $aConfig = array(
		'action' => '#',
		'method' => 'post'
	);

	protected $aElements = array();

	protected $iNumElements = 0;

	/**
	 * Constructor
	 *
	 * @param array $aConfig        	
	 * @return void
	 */
	public function __construct($aConfig = array())
	{
		$this->setConfig($aConfig);
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
	 * Retourne le nombre d'éléments dans la pile.
	 *
	 * @return integer
	 */
	public function getNumElements()
	{
		return $this->iNumElements;
	}

	/**
	 * Ajoute un élément.
	 *
	 * @param Element $oElement        	
	 */
	public function addElement(Element $oElement)
	{
		$this->aElements[] = $oElement;
		
		$this->iNumElements ++;
		
		return $this;
	}

	/**
	 * Réalise le rendu du formulaire.
	 *
	 * @return string
	 */
	public function render()
	{
		$str = '<form action="' . $this->aConfig['action'] . '" method="' . $this->aConfig['method'] . '">';
		
		foreach ($this->aElements as $oElement)
		{
			$str .= $oElement->prepend();
			$str .= $oElement->render();
			$str .= $oElement->append();
		}
		
		$str .= '</form>';
		
		return $str;
	}

	/**
	 * Permet d'ajouter du HTML au formulaire.
	 *
	 * @param string $sHtml        	
	 */
	public function html($sHtml)
	{
		$this->addElement(new ExtraHtml($sHtml));
		
		return $this;
	}

	/**
	 * Ajoute un champs input type text.
	 *
	 * @param array $aConfig        	
	 * @param array $aAttributes        	
	 */
	public function text($aConfig = array(), $aAttributes = array())
	{
		$this->addElement(new InputText($aConfig, $aAttributes));
		
		return $this;
	}

	/**
	 * Ajoute un champs input type password.
	 *
	 * @param array $aConfig        	
	 * @param array $aAttributes        	
	 */
	public function password($aConfig = array(), $aAttributes = array())
	{
		$this->addElement(new InputPassword($aConfig, $aAttributes));
		
		return $this;
	}

	/**
	 * Ajoute un champs input type hidden.
	 *
	 * @param array $aConfig        	
	 * @param array $aAttributes        	
	 */
	public function hidden($aConfig = array(), $aAttributes = array())
	{
		$this->addElement(new InputHidden($aConfig, $aAttributes));
		
		return $this;
	}

	/**
	 * Ajoute un champs textarea.
	 *
	 * @param array $aConfig        	
	 * @param array $aAttributes        	
	 */
	public function textarea($aConfig = array(), $aAttributes = array())
	{
		$this->addElement(new Textarea($aConfig, $aAttributes));
		
		return $this;
	}
}
