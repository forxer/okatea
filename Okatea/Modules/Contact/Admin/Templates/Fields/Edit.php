<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$view->extend('layout');

# Page title and breadcrumb
$okt->page->addTitleTag($okt->module('Contact')->getTitle());
$okt->page->addAriane($okt->module('Contact')->getName(), $view->generateUrl('Contact_index'));

$okt->page->addGlobalTitle(__('m_contact_fields'), $view->generateUrl('Contact_fields'));
$okt->page->addGlobalTitle(__('m_contact_fields_edit_field'));

# button set
$okt->page->setButtonset('fieldBtSt',array(
	'id' => 'contact-field-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_action_Go_back'),
			'url' 			=> $view->generateUrl('Contact_fields'),
			'ui-icon' 		=> 'arrowreturnthick-1-w'
		)
	)
));

?>

<?php # buttons set
echo $okt->page->getButtonSet('fieldBtSt'); ?>

<form action="<?php echo $view->generateUrl('Contact_field_add') ?>" method="post" id="field-definition-form">
	<?php echo $view->render('Contact/Admin/Templates/Fields/DefinitionForm', array(
		'aFieldData' 	=> $aFieldData
	)) ?>
</form>
