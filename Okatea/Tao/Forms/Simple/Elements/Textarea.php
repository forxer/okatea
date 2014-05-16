<?php

/**
 * @class oktFormElementTextarea
 * @ingroup okt_classes_form
 * @brief Textarea definition.
 *
 */
namespace Okatea\Tao\Forms\Simple\Elements;

use Okatea\Tao\Forms\Simple\Element;

class Textarea extends Element
{

	protected $aConfig = array(
		'html' => '<p><label for="{{id}}">{{label}}</label><textarea{{attributes}}>{{value}}</textarea></p>',
		'value' => null,
		'label' => ''
	);

	protected $aAttributes = array();
}
