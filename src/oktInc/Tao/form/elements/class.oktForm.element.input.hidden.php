<?php
/**
 * @class oktFormElementInputHidden
 * @ingroup okt_classes_form
 * @brief Input type hidden definition.
 *
 */

class oktFormElementInputHidden extends oktFormElement
{
	protected $aConfig = array(
		'html' => '<input type="hidden"{{attributes}} value="{{value}}" />',
		'value' => null,
		'label' => ''
	);

	protected $aAttributes = array(
	);


} # class
