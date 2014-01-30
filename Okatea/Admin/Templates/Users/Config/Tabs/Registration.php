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
		if ($("#p_validation").is(":checked")) {
			$("#p_user_choose_group,#p_auto_log_after_registration").attr("disabled", "")
				.parent().addClass("disabled");
		}
		else {
			$("#p_user_choose_group,#p_auto_log_after_registration").removeAttr("disabled")
				.parent().removeClass("disabled");
		}
	}
');

$okt->page->js->addReady('
	handleValidateOptionStatus();
	$("#p_validation").change(function(){handleValidateOptionStatus();});
');

?>

<h3><?php _e('c_a_users_Registration') ?></h3>

	<p class="field"><label for="p_mail_new_registration"><?php echo form::checkbox('p_mail_new_registration', 1, $aPageData['config']['users']['registration']['mail_new_registration']) ?>
	<?php _e('c_a_users_send_mail_new_registration') ?></label></p>

	<p class="field"><label for="p_validation"><?php echo form::checkbox('p_validation', 1, $aPageData['config']['users']['registration']['validation']) ?>
	<?php _e('c_a_users_Validation_of_registration_by_administrator') ?></label></p>

	<p class="field"><label for="p_merge_username_email"><?php echo form::checkbox('p_merge_username_email', 1, $aPageData['config']['users']['registration']['merge_username_email']) ?>
	<?php _e('c_a_users_merge_username_email') ?></label></p>

	<p class="field"><label for="p_auto_log_after_registration"><?php echo form::checkbox('p_auto_log_after_registration', 1, $aPageData['config']['users']['registration']['auto_log_after_registration']) ?>
	<?php _e('c_a_users_auto_log_after_registration') ?></label></p>

	<p class="field"><label for="p_user_choose_group"><?php echo form::checkbox('p_user_choose_group', 1, $aPageData['config']['users']['registration']['user_choose_group']) ?>
	<?php _e('c_a_users_Let_users_choose_their_group') ?></label></p>

	<p class="field"><label for="p_default_group"><?php _e('c_a_users_Default_group') ?></label>
	<?php echo form::select('p_default_group', $aGroups, $aPageData['config']['users']['registration']['default_group']) ?></p>
