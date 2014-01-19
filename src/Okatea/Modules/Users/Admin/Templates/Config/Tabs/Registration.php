<?php

use Okatea\Tao\Forms\Statics\FormElements as form;

?>

<h3><?php _e('m_users_Registration') ?></h3>

	<p class="field"><label for="p_mail_new_registration"><?php echo form::checkbox('p_mail_new_registration',1,$okt->Users->config->mail_new_registration) ?>
	<?php _e('m_users_send_mail_new_registration') ?></label></p>

	<p class="field"><label for="p_validate_users_registration"><?php echo form::checkbox('p_validate_users_registration',1,$okt->Users->config->validate_users_registration) ?>
	<?php _e('m_users_Validation_of_registration_by_administrator') ?></label></p>

	<p class="field"><label for="p_merge_username_email"><?php echo form::checkbox('p_merge_username_email',1,$okt->Users->config->merge_username_email) ?>
	<?php _e('m_users_merge_username_email') ?></label></p>

	<p class="field"><label for="p_auto_log_after_registration"><?php echo form::checkbox('p_auto_log_after_registration',1,$okt->Users->config->auto_log_after_registration) ?>
	<?php _e('m_users_auto_log_after_registration') ?></label></p>

	<p class="field"><label for="p_user_choose_group"><?php echo form::checkbox('p_user_choose_group',1,$okt->Users->config->user_choose_group) ?>
	<?php _e('m_users_Let_users_choose_their_group') ?></label></p>

	<p class="field"><label for="p_default_group"><?php _e('m_users_Default_group') ?></label>
	<?php echo form::select('p_default_group', $aGroups, $okt->Users->config->default_group) ?></p>