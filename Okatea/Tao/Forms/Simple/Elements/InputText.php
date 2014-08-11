<?php

/**
 * @class oktFormElementInputText
 * @ingroup okt_classes_form
 * @brief Input type text definition.
 *
 */
namespace Okatea\Tao\Forms\Simple\Elements;

use Okatea\Tao\Forms\Simple\Element;

class InputText extends Element
{

	protected $aConfig = array(
		'html' => '<p><label for="{{id}}">{{label}}</label><input type="text"{{attributes}} value="{{value}}" /></p>',
		'value' => null,
		'label' => ''
	);

	protected $aAttributes = [];
}
