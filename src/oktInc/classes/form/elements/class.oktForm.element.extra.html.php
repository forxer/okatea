<?php
/**
 * @class oktFormElementExtraHtml
 * @ingroup okt_classes_form
 * @brief Extra HTML in form.
 *
 */

class oktFormElementExtraHtml extends oktFormElement
{
	protected $sHtml;

	/**
	 * Constructor
	 *
	 * @param array $aConfig
	 * @param array $aAttributes
	 * @return void
	 */
	public function __construct($sHtml)
	{
		$this->sHtml = $sHtml;
	}

	/**
	 * Réalise le rendu de l'élément.
	 *
	 * @return string
	 */
	public function render()
	{
		return $this->sHtml;
	}

} # class
