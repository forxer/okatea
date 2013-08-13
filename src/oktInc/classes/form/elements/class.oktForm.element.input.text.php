<?php
/**
 * @class oktFormElementInputText
 * @ingroup okt_classes_form
 * @brief Input type text definition.
 *
 */

class oktFormElementInputText extends oktFormElement
{
	protected $aConfig = array(
		'html' => '<p><label for="{{id}}">{{label}}</label><input type="text"{{attributes}} value="{{value}}" /></p>',
		'value' => null,
		'label' => ''
	);

	protected $aAttributes = array(
	);


} # class
