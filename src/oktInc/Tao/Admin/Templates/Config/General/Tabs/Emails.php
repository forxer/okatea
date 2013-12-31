<?php

use Tao\Forms\Statics\FormElements as form;

# Liste des méthodes d'envoi des courriels
$aEmailTransportsChoice = array(
	__('c_a_config_sending_mail_transport_mail') => 'mail',
	__('c_a_config_sending_mail_transport_smtp') => 'smtp',
	__('c_a_config_sending_mail_transport_mta')  => 'sendmail'
);

# Toggle With Legend
$okt->page->toggleWithLegend('mail_advanced_title', 'mail_advanced_content');

?>

<h3><?php _e('c_a_config_tab_email') ?></h3>

<fieldset>
	<legend><?php _e('c_a_config_email_config') ?></legend>

	<p class="field"><label for="p_email_to" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_a_config_email_to') ?></label>
	<?php echo form::text('p_email_to', 60, 255, $view->escape($okt->config->email['to'])) ?></p>

	<p class="field"><label for="p_email_from" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_a_config_email_from') ?></label>
	<?php echo form::text('p_email_from', 60, 255, $view->escape($okt->config->email['from'])) ?></p>

	<p class="field"><label for="p_email_name"><?php _e('c_a_config_email_name') ?></label>
	<?php echo form::text('p_email_name', 60, 255, $view->escape($okt->config->email['name'])) ?></p>

</fieldset>

<h4 id="mail_advanced_title"><?php _e('c_a_config_sending_mail') ?></h4>

<fieldset id="mail_advanced_content">
	<legend><?php _e('c_a_config_sending_mail_transport') ?></legend>

	<p class="field"><label for="p_email_transport"><?php _e('c_a_config_sending_mail_transport_method') ?></label>
	<?php echo form::select('p_email_transport', $aEmailTransportsChoice, $okt->config->email['transport']) ?></p>

	<fieldset>
		<legend><?php _e('c_a_config_sending_mail_transport_smtp_abbr') ?></legend>

		<p class="field"><label for="p_email_smtp_host"><?php printf(__('c_a_config_sending_mail_transport_smtp_host'),__('c_a_config_sending_mail_transport_smtp_abbr')) ?></label>
		<?php echo form::text('p_email_smtp_host', 60, 255, $view->escape($okt->config->email['smtp']['host'])) ?></p>

		<p class="field"><label for="p_email_smtp_port"><?php printf(__('c_a_config_sending_mail_transport_smtp_port'),__('c_a_config_sending_mail_transport_smtp_abbr')) ?></label>
		<?php echo form::text('p_email_smtp_port', 60, 255, $view->escape($okt->config->email['smtp']['port'])) ?></p>

		<p class="field"><label for="p_email_smtp_username"><?php printf(__('c_a_config_sending_mail_transport_smtp_user'),__('c_a_config_sending_mail_transport_smtp_abbr')) ?></label>
		<?php echo form::text('p_email_smtp_username', 60, 255, $view->escape($okt->config->email['smtp']['username'])) ?></p>

		<p class="field"><label for="p_email_smtp_password"><?php printf(__('c_a_config_sending_mail_transport_smtp_password'),__('c_a_config_sending_mail_transport_smtp_abbr')) ?></label>
		<?php echo form::password('p_email_smtp_password', 60, 255, $view->escape($okt->config->email['smtp']['password'])) ?></p>

	</fieldset>

	<fieldset>
		<legend><?php printf(__('c_a_config_sending_mail_transport_mta_local'),__('c_a_config_sending_mail_transport_mta_abbr'))?></legend>

		<p class="field"><label for="p_email_sendmail"><?php printf(__('c_a_config_sending_mail_transport_mta_command'),__('c_a_config_sending_mail_transport_mta_abbr'))?></label>
		<?php echo form::text('p_email_sendmail', 60, 255, $view->escape($okt->config->email['sendmail'])) ?></p>

	</fieldset>
</fieldset>
