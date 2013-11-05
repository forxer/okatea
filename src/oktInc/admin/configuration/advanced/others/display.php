<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Configuration avancée autres (partie affichage)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;

?>

<h3><?php _e('c_a_config_advanced_tab_others') ?></h3>

<fieldset>
	<legend><?php _e('c_a_config_advanced_maintenance_mode') ?></legend>

	<p class="field"><label for="p_public_maintenance_mode"><?php echo form::checkbox('p_public_maintenance_mode', 1, $okt->config->public_maintenance_mode) ?>
	<?php _e('c_a_config_advanced_enable_public_maintenance_mode') ?></label></p>

	<p class="field"><label for="p_admin_maintenance_mode"><?php echo form::checkbox('p_admin_maintenance_mode', 1, $okt->config->admin_maintenance_mode) ?>
	<?php _e('c_a_config_advanced_enable_admin_maintenance_mode') ?></label></p>

</fieldset>

<fieldset>
	<legend><?php _e('c_a_config_advanced_htmlpurifier') ?></legend>

	<p class="field"><label for="p_htmlpurifier_disabled"><?php echo form::checkbox('p_htmlpurifier_disabled', 1, $okt->config->htmlpurifier_disabled) ?>
	<?php _e('c_a_config_advanced_htmlpurifier_disabled') ?></label></p>

</fieldset>

<fieldset>
	<legend><?php _e('c_a_config_advanced_user_visit_session') ?></legend>

	<p class="field"><label for="p_user_visit_timeout"><?php _e('c_a_config_advanced_user_visit_timeout') ?></label>
	<?php echo form::text('p_user_visit_timeout', 10, 255, html::escapeHTML($okt->config->user_visit['timeout'])) ?></p>

	<p class="field"><label for="p_user_visit_remember_time"><?php _e('c_a_config_advanced_user_visit_remember_time') ?></label>
	<?php echo form::text('p_user_visit_remember_time', 10, 255, html::escapeHTML($okt->config->user_visit['remember_time'])) ?></p>

</fieldset>

<fieldset>
	<legend><?php _e('c_a_config_advanced_logadmin') ?></legend>

	<p><label for="p_log_admin_ttl_months"><?php printf(__('c_a_config_advanced_logadmin_ttl_%s_months'),
	form::text('p_log_admin_ttl_months', 3, 255, html::escapeHTML($okt->config->log_admin['ttl_months']))) ?></label></p>

</fieldset>

<fieldset>
	<legend><?php _e('c_a_config_advanced_news_feed') ?></legend>

	<p class="field"><label for="p_news_feed_enabled"><?php echo form::checkbox('p_news_feed_enabled', 1, $okt->config->news_feed['enabled']) ?>
	<?php _e('c_a_config_advanced_news_feed_enabled') ?></label></p>

	<?php foreach ($okt->languages->list as $aLanguage) : ?>
	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_news_feed_url_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_a_config_advanced_news_feed_url') : printf(__('c_a_config_advanced_news_feed_url_in_%s'), html::escapeHTML($aLanguage['title'])); ?><span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_news_feed_url['.$aLanguage['code'].']','p_news_feed_url_'.$aLanguage['code']), 60, 255, (isset($okt->config->news_feed[$aLanguage['code']]) ? html::escapeHTML($okt->config->news_feed[$aLanguage['code']]) : '')) ?>
	<?php endforeach; ?>

</fieldset>

<fieldset>
	<legend><?php _e('c_a_config_advanced_slug_generation') ?></legend>

	<ul class="checklist">
		<li><label for="p_slug_type_ascii"><?php echo form::radio(array('p_slug_type','p_slug_type_ascii'), 'ascii', ($okt->config->slug_type == 'ascii'))?> <?php _e('c_a_config_advanced_slug_type_ascii') ?></label>
		<span class="note"><?php _e('c_a_config_advanced_slug_type_ascii_example') ?></span></li>
		<li><label for="p_slug_type_utf8"><?php echo form::radio(array('p_slug_type','p_slug_type_utf8'), 'utf8', ($okt->config->slug_type == 'utf8'))?> <?php _e('c_a_config_advanced_slug_type_utf8') ?></label>
		<span class="note"><?php _e('c_a_config_advanced_slug_type_utf8_example') ?></span></li>
	</ul>

</fieldset>