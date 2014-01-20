<?php

use Okatea\Tao\Forms\Statics\FormElements as form;

?>


<h3><?php _e('m_users_General') ?></h3>

<p class="field"><label><?php echo form::checkbox('p_enable_custom_fields', 1, $okt->Users->config->enable_custom_fields) ?>
<?php _e('m_users_Enable_custom_fields') ?></label></p>

<fieldset>
	<legend><?php _e('m_users_Activation_of_public_pages') ?></legend>

	<p class="field"><label><?php echo form::checkbox('p_enable_login_page', 1, $okt->Users->config->enable_login_page) ?>
	<?php _e('m_users_Enable_login_page') ?></label></p>

	<p class="field"><label><?php echo form::checkbox('p_enable_register_page', 1, $okt->Users->config->enable_register_page) ?>
	<?php _e('m_users_Enable_registration_page') ?></label></p>

	<p class="field"><label><?php echo form::checkbox('p_enable_log_reg_page', 1, $okt->Users->config->enable_log_reg_page, '', '', (!$okt->Users->config->enable_login_page || !$okt->Users->config->enable_register_page)) ?>
	<?php _e('m_users_Enable_log_reg_page') ?></label>
	<span class="note"><?php _e('m_users_Enable_log_reg_page_note') ?></span></p>

	<p class="field"><label><?php echo form::checkbox('p_enable_forget_password_page', 1, $okt->Users->config->enable_forget_password_page) ?>
	<?php _e('m_users_Enable_page_forgotten_password') ?></label></p>

	<p class="field"><label><?php echo form::checkbox('p_enable_profile_page', 1, $okt->Users->config->enable_profile_page) ?>
	<?php _e('m_users_Enable_profile_page') ?></label></p>

</fieldset>