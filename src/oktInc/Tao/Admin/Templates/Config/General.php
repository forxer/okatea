<?php

use Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_config_site'));

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered', '.lang-switcher-buttons');
}

# Tabs
$okt->page->tabs();

# Liste des méthodes d'envoi des courriels
$aEmailTransportsChoice = array(
	__('c_a_config_sending_mail_transport_mail') => 'mail',
	__('c_a_config_sending_mail_transport_smtp') => 'smtp',
	__('c_a_config_sending_mail_transport_mta')  => 'sendmail'
);

# Toggle With Legend
$okt->page->toggleWithLegend('mail_advanced_title', 'mail_advanced_content');

?>


<form id="config-site-form" action="<?php $view->generateUrl('config_general') ?>" method="post">
	<div id="tabered">
		<ul>
			<li><a href="#tab_general"><span><?php _e('c_a_config_tab_general') ?></span></a></li>
			<li><a href="#tab_company"><span><?php _e('c_a_config_tab_company') ?></span></a></li>
			<li><a href="#tab_email"><span><?php _e('c_a_config_tab_email') ?></span></a></li>
			<li><a href="#tab_seo"><span><?php _e('c_a_config_tab_seo') ?></span></a></li>
		</ul>

		<div id="tab_general">
			<h3><?php _e('c_a_config_tab_general') ?></h3>

			<?php foreach ($okt->languages->list as $aLanguage) : ?>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>" title="<?php _e('c_c_required_field') ?>" class="required"><?php $okt->languages->unique ? _e('c_a_config_website_title') : printf(__('c_a_config_website_title_in_%s'), $view->escape($aLanguage['title'])); ?><span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, (isset($okt->config->title[$aLanguage['code']]) ? $view->escape($okt->config->title[$aLanguage['code']]) : '')) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_desc_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_a_config_website_desc') : printf(__('c_a_config_website_desc_in_%s'), $view->escape($aLanguage['title'])); ?><span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_desc['.$aLanguage['code'].']','p_desc_'.$aLanguage['code']), 60, 255, (isset($okt->config->desc[$aLanguage['code']]) ? $view->escape($okt->config->desc[$aLanguage['code']]) : '')) ?></p>

			<?php endforeach; ?>
		</div><!-- #tab_general -->

		<div id="tab_company">
			<h3><?php _e('c_a_config_tab_company') ?></h3>

			<div class="two-cols">
				<div class="col">
					<fieldset>
						<legend><?php _e('c_a_config_company') ?></legend>

						<p class="field"><label for="p_company_name"><?php _e('c_a_config_company_name') ?></label>
						<?php echo form::text('p_company_name', 60, 255, $view->escape($okt->config->company['name'])) ?></p>

						<p class="field"><label for="p_company_com_name"><?php _e('c_a_config_company_com_name') ?></label>
						<?php echo form::text('p_company_com_name', 60, 255, $view->escape($okt->config->company['com_name'])) ?></p>

						<p class="field"><label for="p_company_siret"><?php _e('c_a_config_company_siret') ?></label>
						<?php echo form::text('p_company_siret', 60, 255, $view->escape($okt->config->company['siret'])) ?></p>

					</fieldset>
				</div>
				<div class="col">
					<fieldset>
						<legend><?php _e('c_a_config_leader') ?></legend>

						<p class="field"><label for="p_leader_name"><?php _e('c_a_config_leader_name') ?></label>
						<?php echo form::text('p_leader_name', 60, 255, $view->escape($okt->config->leader['name'])) ?></p>

						<p class="field"><label for="p_leader_firstname"><?php _e('c_a_config_leader_firstname') ?></label>
						<?php echo form::text('p_leader_firstname', 60, 255, $view->escape($okt->config->leader['firstname'])) ?></p>
					</fieldset>
				</div>
			</div><!-- .two-cols -->

			<fieldset>
				<legend><?php _e('c_a_config_schedule') ?></legend>

				<?php foreach ($okt->languages->list as $aLanguage) : ?>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_schedule_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_a_config_schedule') : printf(__('c_a_config_schedule_in_%s'), $view->escape($aLanguage['title'])); ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::textarea(array('p_schedule['.$aLanguage['code'].']','p_schedule_'.$aLanguage['code']), 60, 5, (isset($okt->config->schedule[$aLanguage['code']]) ? $view->escape($okt->config->schedule[$aLanguage['code']]) : '')) ?></p>

				<?php endforeach; ?>
			</fieldset>

			<fieldset>
				<legend><?php _e('c_a_config_address') ?></legend>

				<div class="two-cols">
					<p class="field col"><label for="p_address_street"><?php _e('c_a_config_address_street') ?></label>
					<?php echo form::text('p_address_street', 60, 255, $view->escape($okt->config->address['street'])) ?></p>

					<p class="field col"><label for="p_address_street_2"><?php _e('c_a_config_address_street_2') ?></label>
					<?php echo form::text('p_address_street_2', 60, 255, $view->escape($okt->config->address['street_2'])) ?></p>
				</div>

				<div class="two-cols">
					<p class="field col"><label for="p_address_code"><?php _e('c_a_config_address_code') ?></label>
					<?php echo form::text('p_address_code', 10, 255, $view->escape($okt->config->address['code'])) ?></p>

					<p class="field col"><label for="p_address_city"><?php _e('c_a_config_address_city') ?></label>
					<?php echo form::text('p_address_city', 60, 255, $view->escape($okt->config->address['city'])) ?></p>
				</div>

				<div class="two-cols">

					<p class="field col"><label for="p_address_country"><?php _e('c_a_config_address_country') ?></label>
					<?php echo form::text('p_address_country', 60, 255, $view->escape($okt->config->address['country'])) ?></p>
				</div>

				<div class="two-cols">
					<p class="field col"><label for="p_address_tel"><?php _e('c_a_config_address_tel') ?></label>
					<?php echo form::text('p_address_tel', 20, 255, $view->escape($okt->config->address['tel'])) ?></p>

					<p class="field col"><label for="p_address_mobile"><?php _e('c_a_config_address_mobile') ?></label>
					<?php echo form::text('p_address_mobile', 20, 255, $view->escape($okt->config->address['mobile'])) ?></p>
				</div>

				<div class="two-cols">
					<p class="field col"><label for="p_address_fax"><?php _e('c_a_config_address_fax') ?></label>
					<?php echo form::text('p_address_fax', 20, 255, $view->escape($okt->config->address['fax'])) ?></p>
				</div>

			</fieldset>

			<fieldset>
				<legend><?php _e('c_a_config_gps')?></legend>

				<div class="two-cols">
					<div class="col">
						<p class="field"><label for="p_gps_lat">Latitude</label>
						<?php echo form::text('p_gps_lat', 10, 255, $view->escape($okt->config->gps['lat'])) ?></p>
					</div>

					<div class="col">
						<p class="field"><label for="p_gps_long">Longitude</label>
						<?php echo form::text('p_gps_long', 10, 255, $view->escape($okt->config->gps['long'])) ?></p>
					</div>
				</div><!-- .two-cols -->

			</fieldset>
		</div><!-- #tab_company -->

		<div id="tab_email">
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
		</div><!-- #tab_email -->

		<div id="tab_seo">
			<h3><?php _e('c_a_config_tab_seo') ?></h3>

			<?php foreach ($okt->languages->list as $aLanguage) : ?>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_tag_<?php echo $aLanguage['code'] ?>"><?php _e('c_a_config_title_tag') ?><span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title_tag['.$aLanguage['code'].']','p_title_tag_'.$aLanguage['code']), 60, 255, (isset($okt->config->title_tag[$aLanguage['code']]) ? $view->escape($okt->config->title_tag[$aLanguage['code']]) : '')) ?>
			<span class="note"><?php _e('c_a_config_title_tag_note') ?></span></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php _e('c_c_seo_meta_desc') ?><span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, (isset($okt->config->meta_description[$aLanguage['code']]) ? $view->escape($okt->config->meta_description[$aLanguage['code']]) : '')) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php _e('c_c_seo_meta_keywords') ?><span class="lang-switcher-buttons"></span></label>
			<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 57, 5, (isset($okt->config->meta_keywords[$aLanguage['code']]) ? $view->escape($okt->config->meta_keywords[$aLanguage['code']]) : '')) ?></p>

			<?php endforeach; ?>
		</div><!-- #tab_seo -->

	</div><!-- #tabered -->

	<p><?php echo form::hidden(array('form_sent'), 1) ?>
	<?php echo $okt->page->formtoken() ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>
