<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Modules\Contact\Fields;

$view->extend('Layout');

# Page title and breadcrumb
$okt->page->addTitleTag($okt->module('Contact')
	->getTitle());
$okt->page->addAriane($okt->module('Contact')
	->getName(), $view->generateAdminUrl('Contact_index'));

$okt->page->addGlobalTitle(__('m_contact_fields'), $view->generateAdminUrl('Contact_fields'));
$okt->page->addGlobalTitle(__('m_contact_fields_edit_field'));

# button set
$okt->page->setButtonset('fieldBtSt', array(
	'id' => 'contact-field-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('c_c_action_Go_back'),
			'url' => $view->generateAdminUrl('Contact_fields'),
			'ui-icon' => 'arrowreturnthick-1-w'
		),
		array(
			'permission' => true,
			'title' => __('m_contact_fields_add_field'),
			'url' => $view->generateAdminUrl('Contact_field_add'),
			'ui-icon' => 'plusthick'
		),
		array(
			'permission' => true,
			'title' => __('m_contact_fields_edit_values'),
			'url' => $view->generateAdminUrl('Contact_field_values', array(
				'field_id' => $aFieldData['id']
			)),
			'ui-icon' => 'pencil'
		),
		array(
			'permission' => !in_array($aFieldData['id'], Fields::getUnDeletableFields()),
			'title' => __('c_c_action_Delete'),
			'url' => $view->generateAdminUrl('Contact_fields') . '?delete=' . $aFieldData['id'],
			'ui-icon' => 'closethick',
			'onclick' => 'return window.confirm(\'' . $view->escapeJs(__('m_contact_fields_confirm_field_deletion')) . '\')'
		)
	)
));

?>

<?php
# buttons set
echo $okt->page->getButtonSet('fieldBtSt');
?>

<form
	action="<?php echo $view->generateAdminUrl('Contact_field', array('field_id' => $aFieldData['id'])) ?>"
	method="post" id="field-definition-form">
	<?php
	
	echo $view->render('Contact/Admin/Templates/Fields/DefinitionForm', array(
		'aFieldData' => $aFieldData
	))?>
</form>
