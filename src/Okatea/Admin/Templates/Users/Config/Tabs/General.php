<?php

use Okatea\Tao\Forms\Statics\FormElements as form;

?>


<h3><?php _e('m_users_General') ?></h3>

<p class="field"><label><?php echo form::checkbox('p_users_custom_fields_enabled', 1, $okt->Users->config->users_custom_fields_enabled) ?>
<?php _e('m_users_users_custom_fields_enabled') ?></label></p>

<fieldset>
	<legend><?php _e('m_users_Activation_of_public_pages') ?></legend>

	<p class="field"><label><?php echo form::checkbox('p_enable_login_page', 1, $okt->config->users_pages['login']) ?>
	<?php _e('m_users_Enable_login_page') ?></label></p>

	<p class="field"><label><?php echo form::checkbox('p_enable_register_page', 1, $okt->config->users_pages['register']) ?>
	<?php _e('m_users_Enable_registration_page') ?></label></p>

	<p class="field"><label><?php echo form::checkbox('p_enable_log_reg_page', 1, $okt->config->users_pages['log_reg'], '', '', (!$okt->config->users_pages['login'] || !$okt->config->users_pages['register'])) ?>
	<?php _e('m_users_Enable_log_reg_page') ?></label>
	<span class="note"><?php _e('m_users_Enable_log_reg_page_note') ?></span></p>

	<p class="field"><label><?php echo form::checkbox('p_enable_forget_password_page', 1, $okt->config->users_pages['forget_password']) ?>
	<?php _e('m_users_Enable_page_forgotten_password') ?></label></p>

	<p class="field"><label><?php echo form::checkbox('p_enable_profile_page', 1, $okt->config->users_pages['profile']) ?>
	<?php _e('m_users_Enable_profile_page') ?></label></p>

</fieldset>