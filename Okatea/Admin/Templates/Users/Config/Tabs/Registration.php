<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$okt->page->js->addScript('

	function handleValidateOptionStatus() {
		if ($("#p_validation_admin").is(":checked")) {
			$("#p_user_choose_group,#p_auto_log_after_registration").attr("disabled", "")
				.parent().addClass("disabled");
		}
		else {
			$("#p_user_choose_group,#p_auto_log_after_registration").removeAttr("disabled")
				.parent().removeClass("disabled");
		}
	}

	function handleMailNewRegistrationOptionStatus() {
		if ($("#p_mail_new_registration").is(":checked")) {
			$("#p_mail_new_registration_recipients").removeAttr("disabled")
				.parent().removeClass("disabled");
		}
		else {
			$("#p_mail_new_registration_recipients").attr("disabled", "")
				.parent().addClass("disabled");
		}
	}
');

$okt->page->js->addReady('
	handleValidateOptionStatus();
	$("#p_validation_admin").change(function(){handleValidateOptionStatus();});

	handleMailNewRegistrationOptionStatus();
	$("#p_mail_new_registration").change(function(){handleMailNewRegistrationOptionStatus();});
');

$okt->page->css->addFile($okt['public_url'] . '/components/select2/select2.css');
$okt->page->js->addFile($okt['public_url'] . '/components/select2/select2.min.js');
$okt->page->js->addReady('
	$("#p_mail_new_registration_recipients").select2({
		width: "200px",
		closeOnSelect: false
	});
');

?>

<h3><?php _e('c_a_users_Registration') ?></h3>

<p class="field">
	<label for="p_merge_username_email"><?php echo form::checkbox('p_merge_username_email', 1, $aPageData['config']['users']['registration']['merge_username_email'])?>
	<?php _e('c_a_users_merge_username_email') ?></label>
</p>

<p class="field">
	<label for="p_mail_new_registration"><?php echo form::checkbox('p_mail_new_registration', 1, $aPageData['config']['users']['registration']['mail_new_registration'])?>
	<?php _e('c_a_users_send_mail_new_registration') ?></label>
</p>

<p class="field">
	<label for="p_mail_new_registration_recipients"><?php _e('c_a_users_send_mail_new_registration_recipients') ?></label>
	<?php echo form::select(array('p_mail_new_registration_recipients[]','p_mail_new_registration_recipients'), $aUsers, $aPageData['config']['users']['registration']['mail_new_registration_recipients'], null, null, false, true) ?></p>

<p class="field">
	<label for="p_validation_email"><?php echo form::checkbox('p_validation_email', 1, $aPageData['config']['users']['registration']['validation_email'])?>
	<?php _e('c_a_users_Validation_of_registration_by_email') ?></label>
</p>

<p class="field">
	<label for="p_validation_admin"><?php echo form::checkbox('p_validation_admin', 1, $aPageData['config']['users']['registration']['validation_admin'])?>
	<?php _e('c_a_users_Validation_of_registration_by_administrator') ?></label>
</p>

<p class="field">
	<label for="p_auto_log_after_registration"><?php echo form::checkbox('p_auto_log_after_registration', 1, $aPageData['config']['users']['registration']['auto_log_after_registration'])?>
	<?php _e('c_a_users_auto_log_after_registration') ?></label>
</p>

<p class="field">
	<label for="p_user_choose_group"><?php echo form::checkbox('p_user_choose_group', 1, $aPageData['config']['users']['registration']['user_choose_group'])?>
	<?php _e('c_a_users_Let_users_choose_their_group') ?></label>
</p>

<p class="field">
	<label for="p_default_group"><?php _e('c_a_users_Default_group') ?></label>
	<?php echo form::select('p_default_group', $aGroups, $aPageData['config']['users']['registration']['default_group']) ?></p>
