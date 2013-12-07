<?php
/**
 * @class oktFormElementInputHidden
 * @ingroup okt_classes_form
 * @brief Input type hidden definition.
 *
 */

namespace Tao\Forms\Simple\Elements;

use Tao\Forms\Simple\Element;

class InputHidden extends Element
{
	protected $aConfig = array(
		'html' => '<input type="hidden"{{attributes}} value="{{value}}" />',
		'value' => null,
		'label' => ''
	);

	protected $aAttributes = array(
	);


} # class
