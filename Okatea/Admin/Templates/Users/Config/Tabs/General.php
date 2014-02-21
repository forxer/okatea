<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Forms\Statics\FormElements as form;

# JS pour qu'on ne puissent activer la page de connexion/inscription unifiée
# que si les deux pages connexion ET inscription sont activées
$okt->page->js->addScript('
	function setEnableLogRegStatus() {
		if ($("#p_enable_login_page").is(":checked") && $("#p_enable_register_page").is(":checked")) {
			$("#p_enable_log_reg_page").removeAttr("disabled")
				.parent().removeClass("disabled")
				.parent().find(".note").hide();
		} else {
			$("#p_enable_log_reg_page").attr("disabled", "")
				.parent().addClass("disabled")
				.parent().find(".note").show();
		}
	}
');

$okt->page->js->addReady('
	setEnableLogRegStatus();
	$("#p_enable_login_page,#p_enable_register_page").change(function(){setEnableLogRegStatus();});
');

?>


<h3><?php _e('c_a_users_General') ?></h3>

<p class="field"><label><?php echo form::checkbox('p_users_custom_fields_enabled', 1, $aPageData['config']['users']['custom_fields_enabled']) ?>
<?php _e('c_a_users_users_custom_fields_enabled') ?></label></p>

<fieldset>
	<legend><?php _e('c_a_users_Activation_of_public_pages') ?></legend>

	<p class="field"><label><?php echo form::checkbox('p_enable_login_page', 1, $aPageData['config']['users']['pages']['login']) ?>
	<?php _e('c_a_users_Enable_login_page') ?></label></p>

	<p class="field"><label><?php echo form::checkbox('p_enable_register_page', 1, $aPageData['config']['users']['pages']['register']) ?>
	<?php _e('c_a_users_Enable_registration_page') ?></label></p>

	<p class="field"><label><?php echo form::checkbox('p_enable_log_reg_page', 1, $aPageData['config']['users']['pages']['log_reg'], null, null, (!$aPageData['config']['users']['pages']['login'] || !$aPageData['config']['users']['pages']['register'])) ?>
	<?php _e('c_a_users_Enable_log_reg_page') ?></label>
	<span class="note"><?php _e('c_a_users_Enable_log_reg_page_note') ?></span></p>

	<p class="field"><label><?php echo form::checkbox('p_enable_forget_password_page', 1, $aPageData['config']['users']['pages']['forget_password']) ?>
	<?php _e('c_a_users_Enable_page_forgotten_password') ?></label></p>

	<p class="field"><label><?php echo form::checkbox('p_enable_profile_page', 1, $aPageData['config']['users']['pages']['profile']) ?>
	<?php _e('c_a_users_Enable_profile_page') ?></label></p>

</fieldset>
