<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Modules\Contact\Fields;
use Okatea\Tao\Forms\Statics\FormElements as form;

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#field-definition-form', '.lang-switcher-buttons');
}

?>

<div class="two-cols">
	<?php foreach ($okt->languages->list as $aLanguage): ?>
	<p class="field col" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>" title="<?php _e('c_c_required_field') ?>" class="required"><?php $okt->languages->unique ? _e('m_contact_field_title') : printf(__('m_contact_field_title_in_%s'), $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_title['.$aLanguage['code'].']', 'p_title_'.$aLanguage['code']), 60, 255, $view->escape($aFieldData['title'][$aLanguage['code']])) ?></p>
	<?php endforeach; ?>

	<p class="field col"><label for="p_html_id"><?php _e('m_contact_field_html_id')?></label>
	<?php echo form::text('p_html_id', 60, 255, $view->escape($aFieldData['html_id']))?></p>
</div>
<div class="two-cols">
	<p class="field col"><label for="p_type" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_contact_field_type') ?></label>
	<?php echo form::select('p_type', Fields::getFieldsTypes(true), $aFieldData['type'])?></p>

	<p class="field col"><label for="p_active" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_contact_field_status') ?></label>
	<?php echo form::select('p_active', Fields::getFieldsStatus(true, in_array($iFieldId, Fields::getUnDisablableFields())), $aFieldData['active']) ?></p>
</div>

<div class="two-cols">
	<?php foreach ($okt->languages->list as $aLanguage) : ?>
	<p class="field col" lang="<?php echo $aLanguage['code'] ?>"><label for="p_description_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_contact_field_description') : printf(__('m_contact_field_description_in_%s'), $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::textarea(array('p_description['.$aLanguage['code'].']', 'p_description_'.$aLanguage['code']), 58, 5, $view->escape($aFieldData['description'][$aLanguage['code']])) ?></p>
	<?php endforeach; ?>
</div>

<p><?php echo form::hidden('form_sent', 1); ?>
<?php echo $okt->page->formtoken(); ?>
<input type="submit" value="<?php _e('c_c_next') ?>" /></p>
