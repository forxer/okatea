<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Modules\Contact\Fields;
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

# Page title and breadcrumb
$okt->page->addTitleTag($okt->module('Contact')->getTitle());
$okt->page->addAriane($okt->module('Contact')->getName(), $view->generateUrl('Contact_index'));

$okt->page->addGlobalTitle(__('m_contact_fields'), $view->generateUrl('Contact_fields'));
$okt->page->addGlobalTitle(__('m_contact_fields_field_values'));


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
		),
		array(
			'permission' => true,
			'title' 	=> __('m_contact_fields_add_field'),
			'url' 		=> $view->generateUrl('Contact_field_add'),
			'ui-icon' 	=> 'plusthick'
		),
		array(
			'permission' 	=> true,
			'title' 		=> __('m_contact_fields_edit_definition'),
			'url' 			=> $view->generateUrl('Contact_field', array('field_id' => $rsField->id)),
			'ui-icon' 		=> 'pencil'
		),
		array(
			'permission' 	=> !in_array($rsField->id, Fields::getUnDeletableFields()),
			'title' 		=> __('c_c_action_Delete'),
			'url' 			=> $view->generateUrl('Contact_fields').'?delete='.$rsField->id,
			'ui-icon' 		=> 'closethick',
			'onclick' 		=> 'return window.confirm(\''.$view->escapeJs(__('m_contact_fields_confirm_field_deletion')).'\')',
		)
	)
));

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#field-values-form', '.lang-switcher-buttons');
}

?>

<?php # buttons set
echo $okt->page->getButtonSet('fieldBtSt'); ?>

<form action="<?php echo $view->generateUrl('Contact_field_values', array('field_id' => $rsField->id)) ?>" method="post" id="field-values-form">

<?php if ($rsField->isSimpleField()) : ?>

	<p><?php printf(__('m_contact_fields_default_value_of_field_named_%s_of_type_%s'), '<strong>'.$view->escape($rsField->title).'</strong>', '<em>'.$aTypes[$rsField->type].'</em>') ?></p>

	<?php foreach ($okt->languages->list as $aLanguage) : ?>
	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_value_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_contact_fields_default_value') : printf(__('m_contact_fields_default_value_in_%s'), $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_value['.$aLanguage['code'].']','p_value_'.$aLanguage['code']), 60, 255, $view->escape($aValues[$aLanguage['code']])) ?></p>
	<?php endforeach; ?>

<?php else : ?>

	<p><?php printf(__('m_contact_fields_values_of_field_named_%s_of_type_%s'), '<strong>'.$view->escape($rsField->title).'</strong>', '<em>'.$aTypes[$rsField->type].'</em>') ?></p>

	<?php for ($iValueCount = 1; $iValueCount <= $iNumValues; $iValueCount++) : ?>

		<?php foreach ($okt->languages->list as $aLanguage) : ?>

		<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_value_<?php echo $aLanguage['code'].'_'.$iValueCount ?>"><?php $okt->languages->unique ? printf(__('m_contact_fields_value_%s'), $iValueCount) : printf(__('m_contact_fields_value_%s_in_%s'), $iValueCount, $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('p_value['.$aLanguage['code'].']['.$iValueCount.']', 'p_value_'.$aLanguage['code'].'_'.$iValueCount), 60, 255, (!empty($aValues[$aLanguage['code']]) && !empty($aValues[$aLanguage['code']][$iValueCount]) ? $view->escape($aValues[$aLanguage['code']][$iValueCount]) : '')) ?></p>

		<?php endforeach; ?>

	<?php endfor; ?>

<?php endif; ?>

	<p><?php echo form::hidden('form_sent', 1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>

