<?php
/**
 * @class oktFormElementInputPassword
 * @ingroup okt_classes_form
 * @brief Input type password definition.
 *
 */

class oktFormElementInputPassword extends oktFormElement
{
	protected $aConfig = array(
		'html' => '<p><label for="{{id}}">{{label}}</label><input type="password"{{attributes}} value="{{value}}" /></p>',
		'value' => null,
		'label' => ''
	);

	protected $aAttributes = array(
	);


} # class
