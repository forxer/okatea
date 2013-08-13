<?php
/**
 * @class oktFormElementTextarea
 * @ingroup okt_classes_form
 * @brief Textarea definition.
 *
 */

class oktFormElementTextarea extends oktFormElement
{
	protected $aConfig = array(
		'html' => '<p><label for="{{id}}">{{label}}</label><textarea{{attributes}}>{{value}}</textarea></p>',
		'value' => null,
		'label' => ''
	);

	protected $aAttributes = array(
	);

} # class
