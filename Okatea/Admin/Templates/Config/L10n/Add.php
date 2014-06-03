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
$okt->page->addGlobalTitle(__('c_a_config_l10n_add_language'));

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
		)
	)
));

$okt->page->css->addFile($okt->options->public_url . '/components/select2/select2.css');
$okt->page->js->addFile($okt->options->public_url . '/components/select2/select2.min.js');
$okt->page->js->addReady('

	function format(flag) {
		return \'<img class="flag" src="' . $okt->options->public_url . '/img/flags/\' + flag.id + \'" /> <strong>\' + flag.text + \'</strong> - \' + flag.id
	}

	$("#add_img").select2({
		width: "element",
		formatResult: format,
		formatSelection: format,
		escapeMarkup: function(m) { return m; }
	});

	$("#add_code").keyup(function() {
		$("#add_img").val( $(this).val() + ".png" ).trigger("change");
	});

	$("#add_language, #add_country").select2({
		width: "element"
	}).change(function() {
		setLanguageCode();
	});

	function setLanguageCode() {

		var language = $("#add_language option:selected").text();
		var language_code = $("#add_language").val();

		var country = $("#add_country option:selected").text();
		var country_code = $("#add_country").val();

		var title = language;
		var code = language_code;

		if (country_code.length > 0) {
			title = title + " (" + country + ")";
			code = code + "-" + country_code;
		}

		$("#add_title").val(title);
		$("#add_code").val(code);

		$("#add_img").val( language_code + ".png" ).trigger("change");
	}
');

# Buttons
$okt->page->js->addReady('
	$("#add_active_container").buttonset();
');

?>

<?php echo $okt->page->getButtonSet('l10nBtSt'); ?>

<form id="add-language-form"
	action="<?php echo $view->generateUrl('config_l10n_add_language') ?>"
	method="post">
	<h3><?php _e('c_a_config_l10n_add_language') ?></h3>

	<div class="two-cols">
		<p class="field col">
			<label for="add_language"><?php _e('c_a_config_l10n_language') ?></label>
		<?php echo form::select('add_language', $aLanguagesList, $view->escape($aAddLanguageData['language'])) ?></p>

		<p class="field col">
			<label for="add_country"><?php _e('c_a_config_l10n_country') ?></label>
		<?php echo form::select('add_country', $aCountryList, $view->escape($aAddLanguageData['country'])) ?></p>
	</div>

	<div class="two-cols">
		<p class="field col">
			<label for="add_title" title="<?php _e('c_c_required_field') ?>"
				class="required"><?php _e('c_a_config_l10n_title') ?></label>
		<?php echo form::text('add_title', 40, 255, $view->escape($aAddLanguageData['title'])) ?></p>

		<p class="field col">
			<label for="add_code" title="<?php _e('c_c_required_field') ?>"
				class="required"><?php _e('c_a_config_l10n_code') ?></label>
		<?php echo form::text('add_code', 10, 255, $view->escape($aAddLanguageData['code'])) ?></p>
	</div>

	<div class="two-cols">
		<p class="field col">
			<label for="add_img"><?php _e('c_a_config_l10n_icon') ?></label>
		<?php echo form::select('add_img', $aFlags, $view->escape($aAddLanguageData['img'])) ?></p>

		<p id="add_active_container" class="col">
			<?php echo form::radio(array('add_active', 'add_active_1'), 1, ($aAddLanguageData['active'] == 1)) ?><label
				for="add_active_1"><?php _e('c_c_action_Enable') ?></label>
			<?php echo form::radio(array('add_active', 'add_active_0'), 0, ($aAddLanguageData['active'] == 0)) ?><label
				for="add_active_0"><?php _e('c_c_action_Disable') ?></label>
		</p>
	</div>

	<p><?php echo form::hidden('form_sent', 1)?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_add') ?>" />
	</p>
</form>
