<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Configuration du site emails (partie affichage)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;

# Liste des méthodes d'envoi des courriels
$aCourrielTransports = array(
	__('c_a_config_sending_mail_transport_mail') => 'mail',
	__('c_a_config_sending_mail_transport_smtp') => 'smtp',
	__('c_a_config_sending_mail_transport_mta')  => 'sendmail'
);

# Toggle With Legend
$okt->page->toggleWithLegend('mail_advanced_title', 'mail_advanced_content');

?>

<h3><?php _e('c_a_config_tab_email') ?></h3>

<fieldset>
	<legend><?php _e('c_a_config_sender') ?></legend>

	<p class="field"><label for="p_courriel_address"><?php _e('c_a_config_sender_address') ?></label>
	<?php echo form::text('p_courriel_address', 60, 255, html::escapeHTML($okt->config->courriel_address)) ?></p>

	<p class="field"><label for="p_courriel_name"><?php _e('c_a_config_sender_name') ?></label>
	<?php echo form::text('p_courriel_name', 60, 255, html::escapeHTML($okt->config->courriel_name)) ?></p>

	<?php if ($okt->user->is_superadmin) : ?>
	<p class="field"><label for="p_courriel_theme"><?php _e('c_a_config_sender_theme_active') ?></label>
	<?php echo form::radio(array('p_courriel_theme', 'p_courriel_theme_O'), 1, ($okt->config->courriel_theme == 1)) ?>&nbsp;<?php _e('c_c_Yes') ?>&nbsp;&nbsp;
	<?php echo form::radio(array('p_courriel_theme', 'p_courriel_theme_N'), 0, ($okt->config->courriel_theme == 0)) ?>&nbsp;<?php _e('c_c_No') ?></p>
	<?php endif; ?>

</fieldset>

<h4 id="mail_advanced_title"><?php _e('c_a_config_sending_mail') ?></h4>

<fieldset id="mail_advanced_content">
	<legend><?php _e('c_a_config_sending_mail_transport') ?></legend>

	<p class="field"><label for="p_courriel_transport"><?php _e('c_a_config_sending_mail_transport_method') ?></label>
	<?php echo form::select('p_courriel_transport', $aCourrielTransports, $okt->config->courriel_transport) ?></p>

	<fieldset>
		<legend><?php _e('c_a_config_sending_mail_transport_smtp_abbr') ?></legend>

		<p class="field"><label for="p_courriel_smtp_host"><?php printf(__('c_a_config_sending_mail_transport_smtp_host'),__('c_a_config_sending_mail_transport_smtp_abbr')) ?></label>
		<?php echo form::text('p_courriel_smtp_host', 60, 255, html::escapeHTML($okt->config->courriel_smtp['host'])) ?></p>

		<p class="field"><label for="p_courriel_smtp_port"><?php printf(__('c_a_config_sending_mail_transport_smtp_port'),__('c_a_config_sending_mail_transport_smtp_abbr')) ?></label>
		<?php echo form::text('p_courriel_smtp_port', 60, 255, html::escapeHTML($okt->config->courriel_smtp['port'])) ?></p>

		<p class="field"><label for="p_courriel_smtp_username"><?php printf(__('c_a_config_sending_mail_transport_smtp_user'),__('c_a_config_sending_mail_transport_smtp_abbr')) ?></label>
		<?php echo form::text('p_courriel_smtp_username', 60, 255, html::escapeHTML($okt->config->courriel_smtp['username'])) ?></p>

		<p class="field"><label for="p_courriel_smtp_password"><?php printf(__('c_a_config_sending_mail_transport_smtp_password'),__('c_a_config_sending_mail_transport_smtp_abbr')) ?></label>
		<?php echo form::password('p_courriel_smtp_password', 60, 255, html::escapeHTML($okt->config->courriel_smtp['password'])) ?></p>

	</fieldset>

	<fieldset>
		<legend><?php printf(__('c_a_config_sending_mail_transport_mta_local'),__('c_a_config_sending_mail_transport_mta_abbr'))?></legend>

		<p class="field"><label for="p_courriel_sendmail"><?php printf(__('c_a_config_sending_mail_transport_mta_command'),__('c_a_config_sending_mail_transport_mta_abbr'))?></label>
		<?php echo form::text('p_courriel_sendmail', 60, 255, html::escapeHTML($okt->config->courriel_sendmail)) ?></p>

	</fieldset>
</fieldset>
