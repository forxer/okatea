<?php
/**
 * @class oktFormElementExtraHtml
 * @ingroup okt_classes_form
 * @brief Extra HTML in form.
 *
 */

namespace Okatea\Tao\Forms\Simple\Elements;

use Okatea\Tao\Forms\Simple\Element;

class ExtraHtml extends Element
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

}
