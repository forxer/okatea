<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('Layout');

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_config_l10n'), $view->generateUrl('config_l10n'));
$okt->page->addGlobalTitle(__('c_a_config_l10n_edit_language'));

# button set
$okt->page->setButtonset('l10nBtSt', array(
	'id' => 'l10n-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('c_c_action_Go_back'),
			'url' => $view->generateUrl('config_l10n'),
			'ui-icon' => 'arrowreturnthick-1-w'
		),
		array(
			'permission' => true,
			'title' => __('c_a_config_l10n_add_language'),
			'url' => $view->generateUrl('config_l10n_add_language'),
			'ui-icon' => 'plusthick'
		),
		array(
			'permission' => true,
			'title' => __('c_c_action_Delete'),
			'url' => $view->generateUrl('config_l10n') . '?delete=' . $aUpdLanguageData['id'],
			'ui-icon' => 'closethick'
		)
	)
));

$okt->page->css->addFile($okt['public_url'] . '/components/select2/select2.css');
$okt->page->js->addFile($okt['public_url'] . '/components/select2/select2.min.js');
$okt->page->js->addReady('

	function format(flag) {
		return \'<img class="flag" src="' . $okt['public_url'] . '/img/flags/\' + flag.id + \'" /> <strong>\' + flag.text + \'</strong> - \' + flag.id
	}

	$("#edit_img").select2({
		width: "element",
		formatResult: format,
		formatSelection: format,
		escapeMarkup: function(m) { return m; }
	});

	$("#edit_code").keyup(function() {
		$("#edit_img").val( $(this).val() + ".png" ).trigger("change");
	});

');

# Buttons
$okt->page->js->addReady('
	$("#edit_active_container").buttonset();
');

?>

<?php echo $okt->page->getButtonSet('l10nBtSt'); ?>

<form id="edit-language-form"
	action="<?php echo $view->generateUrl('config_l10n_edit_language', array('language_id'=>$aUpdLanguageData['id'])) ?>"
	method="post">
	<h3><?php _e('c_a_config_l10n_edit_language') ?></h3>

	<div class="two-cols">
		<p class="field col">
			<label for="edit_title" title="<?php _e('c_c_required_field') ?>"
				class="required"><?php _e('c_a_config_l10n_title') ?></label>
		<?php echo form::text('edit_title', 40, 255, $view->escape($aUpdLanguageData['title'])) ?></p>

		<p id="edit_active_container" class="col">
			<?php echo form::radio(array('edit_active', 'edit_active_1'), 1, ($aUpdLanguageData['active'] == 1)) ?><label
				for="edit_active_1"><?php _e('c_c_action_Enable') ?></label>
			<?php echo form::radio(array('edit_active', 'edit_active_0'), 0, ($aUpdLanguageData['active'] == 0)) ?><label
				for="edit_active_0"><?php _e('c_c_action_Disable') ?></label>
		</p>
	</div>

	<div class="two-cols">
		<p class="field col">
			<label for="edit_code" title="<?php _e('c_c_required_field') ?>"
				class="required"><?php _e('c_a_config_l10n_code') ?></label>
		<?php echo form::text('edit_code', 10, 255, $view->escape($aUpdLanguageData['code'])) ?></p>

		<p class="field col">
			<label for="edit_img"><?php _e('c_a_config_l10n_icon') ?></label>
		<?php echo form::select('edit_img', $aFlags, $view->escape($aUpdLanguageData['img'])) ?></p>
	</div>

	<p><?php echo form::hidden('form_sent', 1)?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_edit') ?>" />
	</p>
</form>
