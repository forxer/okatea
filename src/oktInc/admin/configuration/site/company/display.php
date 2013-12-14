<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Configuration du site société (partie affichage)
 *
 * @addtogroup Okatea
 *
 */

use Tao\Forms\Statics\FormElements as form;


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;

?>

<h3><?php _e('c_a_config_tab_company') ?></h3>

<div class="two-cols">
	<div class="col">
		<fieldset>
			<legend><?php _e('c_a_config_company') ?></legend>

			<p class="field"><label for="p_company_name"><?php _e('c_a_config_company_name') ?></label>
			<?php echo form::text('p_company_name', 60, 255, html::escapeHTML($okt->config->company['name'])) ?></p>

			<p class="field"><label for="p_company_com_name"><?php _e('c_a_config_company_com_name') ?></label>
			<?php echo form::text('p_company_com_name', 60, 255, html::escapeHTML($okt->config->company['com_name'])) ?></p>

			<p class="field"><label for="p_company_siret"><?php _e('c_a_config_company_siret') ?></label>
			<?php echo form::text('p_company_siret', 60, 255, html::escapeHTML($okt->config->company['siret'])) ?></p>

		</fieldset>
	</div>
	<div class="col">
		<fieldset>
			<legend><?php _e('c_a_config_leader') ?></legend>

			<p class="field"><label for="p_leader_name"><?php _e('c_a_config_leader_name') ?></label>
			<?php echo form::text('p_leader_name', 60, 255, html::escapeHTML($okt->config->leader['name'])) ?></p>

			<p class="field"><label for="p_leader_firstname"><?php _e('c_a_config_leader_firstname') ?></label>
			<?php echo form::text('p_leader_firstname', 60, 255, html::escapeHTML($okt->config->leader['firstname'])) ?></p>
		</fieldset>
	</div>
</div><!-- .two-cols -->

<fieldset>
	<legend><?php _e('c_a_config_schedule') ?></legend>

	<?php foreach ($okt->languages->list as $aLanguage) : ?>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_schedule_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_a_config_schedule') : printf(__('c_a_config_schedule_in_%s'), html::escapeHTML($aLanguage['title'])); ?><span class="lang-switcher-buttons"></span></label>
	<?php echo form::textarea(array('p_schedule['.$aLanguage['code'].']','p_schedule_'.$aLanguage['code']), 60, 5, (isset($okt->config->schedule[$aLanguage['code']]) ? html::escapeHTML($okt->config->schedule[$aLanguage['code']]) : '')) ?></p>

	<?php endforeach; ?>
</fieldset>

<fieldset>
	<legend><?php _e('c_a_config_address') ?></legend>

	<div class="two-cols">
		<p class="field col"><label for="p_address_street"><?php _e('c_a_config_address_street') ?></label>
		<?php echo form::text('p_address_street', 60, 255, html::escapeHTML($okt->config->address['street'])) ?></p>

		<p class="field col"><label for="p_address_street_2"><?php _e('c_a_config_address_street_2') ?></label>
		<?php echo form::text('p_address_street_2', 60, 255, html::escapeHTML($okt->config->address['street_2'])) ?></p>
	</div>

	<div class="two-cols">
		<p class="field col"><label for="p_address_code"><?php _e('c_a_config_address_code') ?></label>
		<?php echo form::text('p_address_code', 10, 255, html::escapeHTML($okt->config->address['code'])) ?></p>

		<p class="field col"><label for="p_address_city"><?php _e('c_a_config_address_city') ?></label>
		<?php echo form::text('p_address_city', 60, 255, html::escapeHTML($okt->config->address['city'])) ?></p>
	</div>

	<div class="two-cols">

		<p class="field col"><label for="p_address_country"><?php _e('c_a_config_address_country') ?></label>
		<?php echo form::text('p_address_country', 60, 255, html::escapeHTML($okt->config->address['country'])) ?></p>
	</div>

	<div class="two-cols">
		<p class="field col"><label for="p_address_tel"><?php _e('c_a_config_address_tel') ?></label>
		<?php echo form::text('p_address_tel', 20, 255, html::escapeHTML($okt->config->address['tel'])) ?></p>

		<p class="field col"><label for="p_address_mobile"><?php _e('c_a_config_address_mobile') ?></label>
		<?php echo form::text('p_address_mobile', 20, 255, html::escapeHTML($okt->config->address['mobile'])) ?></p>
	</div>

	<div class="two-cols">
		<p class="field col"><label for="p_address_fax"><?php _e('c_a_config_address_fax') ?></label>
		<?php echo form::text('p_address_fax', 20, 255, html::escapeHTML($okt->config->address['fax'])) ?></p>
	</div>

</fieldset>

<fieldset>
	<legend><?php _e('c_a_config_gps')?></legend>

	<div class="two-cols">
		<div class="col">
			<p class="field"><label for="p_gps_lat">Latitude</label>
			<?php echo form::text('p_gps_lat', 10, 255, html::escapeHTML($okt->config->gps['lat'])) ?></p>
		</div>

		<div class="col">
			<p class="field"><label for="p_gps_long">Longitude</label>
			<?php echo form::text('p_gps_long', 10, 255, html::escapeHTML($okt->config->gps['long'])) ?></p>
		</div>
	</div><!-- .two-cols -->

</fieldset>
