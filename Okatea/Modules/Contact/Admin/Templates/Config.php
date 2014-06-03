<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('Layout');

# Module title tag
$okt->page->addTitleTag($okt->module('Contact')
	->getTitle());

# Module start breadcrumb
$okt->page->addAriane($okt->module('Contact')
	->getName(), $view->generateUrl('Contact_index'));

# Titre de la page
$okt->page->addGlobalTitle(__('m_contact_config_configuration'));

# Lockable
$okt->page->lockable();

# Onglets
$okt->page->tabs();

# Lang switcher
if (! $okt->languages->unique)
{
	$okt->page->langSwitcher('#tabered', '.lang-switcher-buttons');
}

# Loader
$okt->page->loader('.lazy-load');

# Color picker
$okt->page->css->addFile($okt->options->public_url . '/components/spectrum/spectrum.css');
$okt->page->js->addFile($okt->options->public_url . '/components/spectrum/spectrum.js');

$okt->page->js->addReady('
	$("#p_email_color").spectrum({

		preferredFormat: "hex",

		showInitial: true,
		showInput: true,

		cancelText: "' . $view->escapeJs(__('c_c_action_cancel')) . '",
		chooseText: "' . $view->escapeJs(__('c_c_action_choose')) . '",

		change: function(color) {
			$(this).val(color.toHexString());
		},
		move: function(color) {
			$(this).val(color.toHexString());
		}
	})
	.show()
	;
');
?>

<form action="<?php $view->generateUrl('Contact_config') ?>"
	method="post">
	<div id="tabered">
		<ul>
			<li><a href="#tab_general"><span><?php _e('m_contact_config_general') ?></span></a></li>
			<li><a href="#tab_map"><span><?php _e('m_contact_config_access_map') ?></span></a></li>
			<li><a href="#tab_tpl"><span><?php _e('m_contact_config_templates') ?></span></a></li>
			<li><a href="#tab_seo"><span><?php _e('c_c_seo') ?></span></a></li>
		</ul>

		<div id="tab_general">
			<h3><?php _e('m_contact_config_general') ?></h3>

			<ul class="checklist"><?php _e('m_contact_config_from_to_choice')?>
				<li><label><?php echo form::radio(array('p_from_to'), 'website', ($okt->module('Contact')->config->from_to == 'website')) ?><?php _e('m_contact_config_from_to_website') ?></label></li>
				<li><label><?php echo form::radio(array('p_from_to'), 'user', ($okt->module('Contact')->config->from_to =='user')) ?><?php _e('m_contact_config_from_to_user') ?></label></li>
			</ul>

			<fieldset>
				<legend><?php _e('m_contact_config_Captcha') ?></legend>
			<?php if ($okt->page->hasCaptcha()) : ?>
				<p class="field">
					<label for="p_captcha"><?php _e('m_contact_config_Captcha') ?></label>
				<?php echo form::select('p_captcha', array_merge(array(__('c_c_Disabled') => 0), $okt->page->getCaptchaList(true)), $okt->module('Contact')->config->captcha) ?></p>
			<?php else : ?>
				<p><?php _e('m_contact_config_no_captcha')?>
				<?php echo form::hidden('p_captcha', 0); ?></p>
			<?php endif; ?>
			</fieldset>

			<fieldset>
				<legend><?php _e('m_contact_config_email_image') ?></legend>

				<p class="field">
					<label for="p_email_color"><?php echo __('m_contact_config_email_color') ?></label>
				<?php echo form::text('p_email_color', 10, 7, $okt->module('Contact')->config->email_color) ?></p>

				<p class="field">
					<label for="p_email_color"><?php echo __('m_contact_config_email_font_size') ?></label>
					<select name="p_email_size" id="p_email_size">
					<?php for($i=8; $i<=16; $i++) : ?>
					<option value="<?php echo $i; ?>"
							<?php if ($okt->module('Contact')->config->email_size == $i) : ?>
							selected="selected" <?php endif; ?>><?php echo $i; ?> px</option>
					<?php endfor; ?>
				</select>
				</p>
			</fieldset>

		</div>
		<!-- #tab_general -->

		<div id="tab_map">

			<h3><?php _e('m_contact_config_access_map') ?></h3>

			<fieldset>
				<legend><?php _e('m_contact_config_activation_access_map') ?></legend>

				<p class="field">
					<label><?php echo form::checkbox('p_enable_google_map', 1, (!$bGoogleMapNotEnablable ? $okt->module('Contact')->config->google_map['enable'] : false), '', '', $bGoogleMapNotEnablable)?>
				<?php _e('m_contact_config_enable_access_map') ?></label>

				<?php if ($bGoogleMapNotEnablable) : ?>
				<span class="note"><?php printf(__('m_contact_config_enable_access_map_note'), $view->generateUrl('config_general')) ?></span>
				<?php endif; ?></p>

			</fieldset>

			<fieldset>
				<legend><?php _e('m_contact_config_display_access_map') ?></legend>

				<ul class="checklist"><?php _e('m_contact_config_display_mode_access_map')?>
					<li><label><?php echo form::radio(array('p_google_map_display'), 'link', ($okt->module('Contact')->config->google_map['display'] == 'link')) ?><?php _e('m_contact_config_display_mode_link') ?></label></li>
					<li><label><?php echo form::radio(array('p_google_map_display'), 'inside', ($okt->module('Contact')->config->google_map['display'] == 'inside')) ?><?php _e('m_contact_config_display_mode_other_page') ?></label></li>
					<li><label><?php echo form::radio(array('p_google_map_display'), 'other_page', ($okt->module('Contact')->config->google_map['display'] == 'other_page')) ?><?php _e('m_contact_config_display_mode_inside') ?></label></li>
				</ul>

				<p class="field">
					<label for="p_google_map_zoom"><?php _e('m_contact_config_zoom_access_map') ?></label>
				<?php echo form::text('p_google_map_zoom', 5, 255, $view->escape($okt->module('Contact')->config->google_map['options']['zoom'])) ?></p>

				<ul class="checklist"><?php _e('m_contact_config_mode_access_map')?>
					<li><label><?php echo form::radio(array('p_google_map_mode'), 'ROADMAP', ($okt->module('Contact')->config->google_map['options']['mode'] == 'ROADMAP')) ?> <?php _e('m_contact_config_mode_access_map_roadmap') ?></label></li>
					<li><label><?php echo form::radio(array('p_google_map_mode'), 'SATELLITE', ($okt->module('Contact')->config->google_map['options']['mode'] == 'SATELLITE')) ?> <?php _e('m_contact_config_mode_access_map_satellite') ?></label></li>
					<li><label><?php echo form::radio(array('p_google_map_mode'), 'HYBRID', ($okt->module('Contact')->config->google_map['options']['mode'] == 'HYBRID')) ?> <?php _e('m_contact_config_mode_access_map_hybrid') ?></label></li>
					<li><label><?php echo form::radio(array('p_google_map_mode'), 'TERRAIN', ($okt->module('Contact')->config->google_map['options']['mode'] == 'TERRAIN')) ?> <?php _e('m_contact_config_mode_access_map_terrain') ?></label></li>
				</ul>

			</fieldset>

		</div>
		<!-- #tab_map -->

		<div id="tab_tpl">
			<h3><?php _e('m_contact_config_templates_title') ?></h3>

			<h4><?php _e('m_contact_config_templates_contact') ?></h4>

			<?php echo $oTemplatesContact->getHtmlConfigUsablesTemplates(false); ?>

			<h4><?php _e('m_contact_config_templates_map') ?></h4>

			<?php echo $oTemplatesMap->getHtmlConfigUsablesTemplates(false); ?>

		</div>
		<!-- #tab_tpl -->

		<div id="tab_seo">

			<h3><?php _e('c_c_seo_help') ?></h3>

			<div class="two-cols">

				<div class="col">

					<h4><?php _e('m_contact_config_seo_contact') ?></h4>

					<fieldset>
						<legend><?php _e('c_c_seo_identity_meta') ?></legend>

						<?php foreach ($okt->languages->list as $aLanguage) : ?>
						<p class="field" lang="<?php echo $aLanguage['code'] ?>">
							<label for="p_name_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_intitle_in_%s'), $view->escape($aLanguage['title'])) ?><span
								class="lang-switcher-buttons"></span></label>
						<?php echo form::text(array('p_name['.$aLanguage['code'].']','p_name_'.$aLanguage['code']), 60, 255, (isset($okt->module('Contact')->config->name[$aLanguage['code']]) ? $view->escape($okt->module('Contact')->config->name[$aLanguage['code']]) : '')) ?></p>

						<p class="field" lang="<?php echo $aLanguage['code'] ?>">
							<label for="p_title_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_title_tag_in_%s'), $view->escape($aLanguage['title'])) ?><span
								class="lang-switcher-buttons"></span></label>
						<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, (isset($okt->module('Contact')->config->title[$aLanguage['code']]) ? $view->escape($okt->module('Contact')->config->title[$aLanguage['code']]) : '')) ?></p>

						<p class="field" lang="<?php echo $aLanguage['code'] ?>">
							<label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_meta_desc_in_%s'), $view->escape($aLanguage['title'])) ?><span
								class="lang-switcher-buttons"></span></label>
						<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, (isset($okt->module('Contact')->config->meta_description[$aLanguage['code']]) ? $view->escape($okt->module('Contact')->config->meta_description[$aLanguage['code']]) : '')) ?></p>

						<p class="field" lang="<?php echo $aLanguage['code'] ?>">
							<label for="p_name_seo_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_title_seo_in_%s'), $view->escape($aLanguage['title'])) ?><span
								class="lang-switcher-buttons"></span></label>
						<?php echo form::text(array('p_name_seo['.$aLanguage['code'].']','p_name_seo_'.$aLanguage['code']), 60, 255, (isset($okt->module('Contact')->config->name_seo[$aLanguage['code']]) ? $view->escape($okt->module('Contact')->config->name_seo[$aLanguage['code']]) : '')) ?></p>

						<p class="field" lang="<?php echo $aLanguage['code'] ?>">
							<label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_meta_keywords_in_%s'), $view->escape($aLanguage['title'])) ?><span
								class="lang-switcher-buttons"></span></label>
						<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 57, 5, (isset($okt->module('Contact')->config->meta_keywords[$aLanguage['code']]) ? $view->escape($okt->module('Contact')->config->meta_keywords[$aLanguage['code']]) : '')) ?></p>

						<?php endforeach; ?>
					</fieldset>
				</div>

				<div class="col">

					<h4><?php _e('m_contact_config_seo_map') ?></h4>

					<fieldset>
						<legend><?php _e('c_c_seo_identity_meta') ?></legend>

						<?php foreach ($okt->languages->list as $aLanguage) : ?>
						<p class="field" lang="<?php echo $aLanguage['code'] ?>">
							<label for="p_name_map_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_intitle_in_%s'), $view->escape($aLanguage['title'])) ?><span
								class="lang-switcher-buttons"></span></label>
						<?php echo form::text(array('p_name_map['.$aLanguage['code'].']','p_name_map_'.$aLanguage['code']), 60, 255, (isset($okt->module('Contact')->config->name_map[$aLanguage['code']]) ? $view->escape($okt->module('Contact')->config->name_map[$aLanguage['code']]) : '')) ?></p>

						<p class="field" lang="<?php echo $aLanguage['code'] ?>">
							<label for="p_title_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_title_tag_in_%s'), $view->escape($aLanguage['title'])) ?><span
								class="lang-switcher-buttons"></span></label>
						<?php echo form::text(array('p_title_map['.$aLanguage['code'].']','p_title_map_'.$aLanguage['code']), 60, 255, (isset($okt->module('Contact')->config->title_map[$aLanguage['code']]) ? $view->escape($okt->module('Contact')->config->title_map[$aLanguage['code']]) : '')) ?></p>

						<p class="field" lang="<?php echo $aLanguage['code'] ?>">
							<label
								for="p_meta_description_map_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_meta_desc_in_%s'), $view->escape($aLanguage['title'])) ?><span
								class="lang-switcher-buttons"></span></label>
						<?php echo form::text(array('p_meta_description_map['.$aLanguage['code'].']','p_meta_description_map_'.$aLanguage['code']), 60, 255, (isset($okt->module('Contact')->config->meta_description_map[$aLanguage['code']]) ? $view->escape($okt->module('Contact')->config->meta_description_map[$aLanguage['code']]) : '')) ?></p>

						<p class="field" lang="<?php echo $aLanguage['code'] ?>">
							<label for="p_name_seo_map_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_title_seo_in_%s'), $view->escape($aLanguage['title'])) ?><span
								class="lang-switcher-buttons"></span></label>
						<?php echo form::text(array('p_name_seo_map['.$aLanguage['code'].']','p_name_seo_map_'.$aLanguage['code']), 60, 255, (isset($okt->module('Contact')->config->name_seo_map[$aLanguage['code']]) ? $view->escape($okt->module('Contact')->config->name_seo_map[$aLanguage['code']]) : '')) ?></p>

						<p class="field" lang="<?php echo $aLanguage['code'] ?>">
							<label for="p_meta_keywords_map_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_meta_keywords_in_%s'), $view->escape($aLanguage['title'])) ?><span
								class="lang-switcher-buttons"></span></label>
						<?php echo form::textarea(array('p_meta_keywords_map['.$aLanguage['code'].']','p_meta_keywords_map_'.$aLanguage['code']), 57, 5, (isset($okt->module('Contact')->config->meta_keywords_map[$aLanguage['code']]) ? $view->escape($okt->module('Contact')->config->meta_keywords_map[$aLanguage['code']]) : '')) ?></p>

						<?php endforeach; ?>
					</fieldset>

				</div>

			</div>
			<!-- .two-cols -->

		</div>
		<!-- #tab_seo -->

	</div>
	<!-- #tabered -->

	<p><?php echo form::hidden('form_sent', 1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" />
	</p>
</form>
