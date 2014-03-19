<?php
/**
 * @ingroup okt_module_contact
 * @brief Page de configuration du module
 *
 */


use Okatea\Admin\Page;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Themes\TemplatesSet;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# google map activable ?
$bGoogleMapNotEnablable = ($okt->config->address['street'] == '' || $okt->config->address['code'] == '' || $okt->config->address['city'] == '');

# Gestionnaires de templates
$oTemplatesContact = new TemplatesSet($okt,
	$okt->contact->config->templates['contact'],
	'contact/contact',
	'contact',
	'module.php?m=contact&amp;action=config&amp;'
);

$oTemplatesMap = new TemplatesSet($okt,
	$okt->contact->config->templates['map'],
	'contact/map',
	'map',
	'module.php?m=contact&amp;action=config&amp;'
);



/* Traitements
----------------------------------------------------------*/

# enregistrement configuration
if (!empty($_POST['form_sent']))
{
	$p_captcha = !empty($_POST['p_captcha']) ? $_POST['p_captcha'] : '';
	$p_from_to = !empty($_POST['p_from_to']) ? $_POST['p_from_to'] : '';
	$p_mail_color = !empty($_POST['p_mail_color']) ? $_POST['p_mail_color'] : '000000';
	$p_mail_size = !empty($_POST['p_mail_size']) ? $_POST['p_mail_size'] : '12';

	$p_tpl_contact = $oTemplatesContact->getPostConfig();
	$p_tpl_map = $oTemplatesMap->getPostConfig();

	$p_name = !empty($_POST['p_name']) && is_array($_POST['p_name']) ? $_POST['p_name'] : array();
	$p_name_seo = !empty($_POST['p_name_seo']) && is_array($_POST['p_name_seo'])  ? $_POST['p_name_seo'] : array();
	$p_title = !empty($_POST['p_title']) && is_array($_POST['p_title']) ? $_POST['p_title'] : array();
	$p_meta_description = !empty($_POST['p_meta_description']) && is_array($_POST['p_meta_description']) ? $_POST['p_meta_description'] : array();
	$p_meta_keywords = !empty($_POST['p_meta_keywords']) && is_array($_POST['p_meta_keywords']) ? $_POST['p_meta_keywords'] : array();

	$p_name_map = !empty($_POST['p_name_map']) && is_array($_POST['p_name_map']) ? $_POST['p_name_map'] : array();
	$p_name_seo_map = !empty($_POST['p_name_seo_map']) && is_array($_POST['p_name_seo_map'])  ? $_POST['p_name_seo_map'] : array();
	$p_title_map = !empty($_POST['p_title_map']) && is_array($_POST['p_title_map']) ? $_POST['p_title_map'] : array();
	$p_meta_description_map = !empty($_POST['p_meta_description_map']) && is_array($_POST['p_meta_description_map']) ? $_POST['p_meta_description_map'] : array();
	$p_meta_keywords_map = !empty($_POST['p_meta_keywords_map']) && is_array($_POST['p_meta_keywords_map']) ? $_POST['p_meta_keywords_map'] : array();

	$p_enable_google_map = !empty($_POST['p_enable_google_map']) ? true : false;
	$p_google_map_display = !empty($_POST['p_google_map_display']) ? $_POST['p_google_map_display'] : 'inside';
	$p_google_map_zoom = !empty($_POST['p_google_map_zoom']) ? $_POST['p_google_map_zoom'] : 14;
	$p_google_map_mode = !empty($_POST['p_google_map_mode']) ? $_POST['p_google_map_mode'] : 'SATELLITE';

	if ($okt->error->isEmpty())
	{
		$aNewConf = array(
			'captcha' => $p_captcha,
			'from_to' => $p_from_to,

			'mail_color' => $p_mail_color,
			'mail_size' => $p_mail_size,

			'templates' => array(
				'contact' => $p_tpl_contact,
				'map' => $p_tpl_map
			),

			'name' => $p_name,
			'name_seo' => $p_name_seo,
			'title' => $p_title,

			'meta_description' => $p_meta_description,
			'meta_keywords' => $p_meta_keywords,

			'name_map' => $p_name_map,
			'name_seo_map' => $p_name_seo_map,
			'title_map' => $p_title_map,

			'meta_description_map' => $p_meta_description_map,
			'meta_keywords_map' => $p_meta_keywords_map,

			'google_map' => array(
				'enable' => (boolean)$p_enable_google_map,
				'display' => $p_google_map_display,
				'options' => array(
					'zoom' => (integer)$p_google_map_zoom,
					'mode' => $p_google_map_mode
				)
			)
		);

		try
		{
			$okt->contact->config->write($aNewConf);

			$okt->page->flash->success(__('c_c_confirm_configuration_updated'));

			http::redirect('module.php?m=contact&action=config');
		}
		catch (InvalidArgumentException $e)
		{
			$okt->error->set(__('c_c_error_writing_configuration'));
			$okt->error->set($e->getMessage());
		}
	}
}



/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('m_contact_configuration'));

# Lockable
$okt->page->lockable();

# Onglets
$okt->page->tabs();

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered','.lang-switcher-buttons');
}

# Color picker
$okt->page->colorpicker('#p_mail_color');
$okt->page->js->addReady('
	$("#tabs_config").bind("tabsshow", function() {
		$(".jPicker.Container").css({"top":"300px"});
	});
');


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post">
	<div id="tabered">
		<ul>
			<li><a href="#tab_general"><span><?php _e('m_contact_General') ?></span></a></li>
			<li><a href="#tab_map"><span><?php _e('m_contact_access_map') ?></span></a></li>
			<li><a href="#tab_tpl"><span><?php _e('m_contact_config_tab_tpl') ?></span></a></li>
			<li><a href="#tab_seo"><span><?php _e('c_c_seo') ?></span></a></li>
		</ul>

		<div id="tab_general">
			<h3><?php _e('m_contact_General') ?></h3>

			<ul class="checklist"><?php _e('m_contact_from_to_choice') ?>
				<li><label><?php echo form::radio(array('p_from_to'),'website',($okt->contact->config->from_to=='website')) ?><?php _e('m_contact_from_to_website') ?></label></li>
				<li><label><?php echo form::radio(array('p_from_to'),'user',($okt->contact->config->from_to=='user')) ?><?php _e('m_contact_from_to_user') ?></label></li>
			</ul>

			<?php if ($okt->page->hasCaptcha()) : ?>
				<p class="field"><label for="p_captcha"><?php _e('m_contact_Captcha') ?></label>
				<?php echo form::select('p_captcha',array_merge(array(__('c_c_Disabled')=>0),$okt->page->getCaptchaList(true)),$okt->contact->config->captcha) ?></p>
			<?php else : ?>
				<p><?php _e('m_contact_no_captcha') ?>
				<?php echo form::hidden('p_captcha',0); ?></p>
			<?php endif;?>

			<p class="field"><label class="inline3" for="p_mail_color"><?php echo __('m_contact_email') ?> : </label>
			<input type="text" id="p_mail_color" name="p_mail_color" value="<?php echo $okt->contact->config->mail_color ?>" size="10" maxlength="6" />&nbsp;&nbsp;
			<select name="p_mail_size" id="p_mail_size">
				<?php for($i=8; $i<=16; $i++) : ?>
				<option value="<?php echo $i; ?>" <?php if ($okt->contact->config->mail_size == $i) { ?>selected="selected"<?php } ?>><?php echo $i; ?> px</option>
				<?php endfor; ?>
			</select></p>

		</div><!-- #tab_general -->

		<div id="tab_map">

			<h3><?php _e('m_contact_access_map') ?></h3>

			<fieldset>
				<legend><?php _e('m_contact_activation_access_map') ?></legend>

				<p class="field"><label><?php echo form::checkbox('p_enable_google_map', 1, (!$bGoogleMapNotEnablable ? $okt->contact->config->google_map['enable'] : false), '', '', $bGoogleMapNotEnablable) ?>
				<?php _e('m_contact_enable_access_map') ?></label>

				<?php if ($bGoogleMapNotEnablable) : ?>
				<span class="note">Les <a href="configuration.php?action=site#tab_company">informations de la société</a> ne sont pas renseignées, cette fonctionnalité ne peut être activée.</span>
				<?php endif; ?></p>

			</fieldset>

			<fieldset>
				<legend><?php _e('m_contact_display_access_map') ?></legend>

				<ul class="checklist"><?php _e('m_contact_display_mode_access_map') ?>
					<li><label><?php echo form::radio(array('p_google_map_display'),'link',($okt->contact->config->google_map['display']=='link')) ?><?php _e('m_contact_display_mode_link') ?></label></li>
					<li><label><?php echo form::radio(array('p_google_map_display'),'inside',($okt->contact->config->google_map['display']=='inside')) ?><?php _e('m_contact_display_mode_inside') ?></label></li>
					<li><label><?php echo form::radio(array('p_google_map_display'),'other_page',($okt->contact->config->google_map['display']=='other_page')) ?><?php _e('m_contact_display_mode_other_page') ?></label></li>
				</ul>

				<p class="field"><label for="p_google_map_zoom"><?php _e('m_contact_zoom_access_map') ?></label>
				<?php echo form::text('p_google_map_zoom', 5, 255, html::escapeHTML($okt->contact->config->google_map['options']['zoom'])) ?></p>

				<ul class="checklist"><?php _e('m_contact_mode_access_map')?>
					<li><label><?php echo form::radio(array('p_google_map_mode'),'ROADMAP',($okt->contact->config->google_map['options']['mode']=='ROADMAP')) ?> <?php _e('m_contact_mode_access_map_roadmap') ?></label></li>
					<li><label><?php echo form::radio(array('p_google_map_mode'),'SATELLITE',($okt->contact->config->google_map['options']['mode']=='SATELLITE')) ?> <?php _e('m_contact_mode_access_map_satellite') ?></label></li>
					<li><label><?php echo form::radio(array('p_google_map_mode'),'HYBRID',($okt->contact->config->google_map['options']['mode']=='HYBRID')) ?> <?php _e('m_contact_mode_access_map_hybrid') ?></label></li>
					<li><label><?php echo form::radio(array('p_google_map_mode'),'TERRAIN',($okt->contact->config->google_map['options']['mode']=='TERRAIN')) ?> <?php _e('m_contact_mode_access_map_terrain') ?></label></li>
				</ul>

			</fieldset>

		</div><!-- #tab_map -->

		<div id="tab_tpl">
			<h3><?php _e('m_contact_config_tab_tpl_title') ?></h3>

			<h4><?php _e('m_contact_config_tpl_contact') ?></h4>

			<?php echo $oTemplatesContact->getHtmlConfigUsablesTemplates(false); ?>

			<h4><?php _e('m_contact_config_tpl_map') ?></h4>

			<?php echo $oTemplatesMap->getHtmlConfigUsablesTemplates(false); ?>

		</div><!-- #tab_tpl -->

		<div id="tab_seo">
			<h3><?php _e('c_c_seo_help') ?></h3>

			<h4><?php _e('m_contact_seo_contact') ?></h4>

			<fieldset>
				<legend><?php _e('c_c_seo_identity_meta') ?></legend>

				<?php foreach ($okt->languages->list as $aLanguage) : ?>
				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_intitle_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name['.$aLanguage['code'].']','p_name_'.$aLanguage['code']), 60, 255, (isset($okt->contact->config->name[$aLanguage['code']]) ? html::escapeHTML($okt->contact->config->name[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_title_tag_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, (isset($okt->contact->config->title[$aLanguage['code']]) ? html::escapeHTML($okt->contact->config->title[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_meta_desc_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, (isset($okt->contact->config->meta_description[$aLanguage['code']]) ? html::escapeHTML($okt->contact->config->meta_description[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_seo_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_title_seo_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name_seo['.$aLanguage['code'].']','p_name_seo_'.$aLanguage['code']), 60, 255, (isset($okt->contact->config->name_seo[$aLanguage['code']]) ? html::escapeHTML($okt->contact->config->name_seo[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_meta_keywords_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 57, 5, (isset($okt->contact->config->meta_keywords[$aLanguage['code']]) ? html::escapeHTML($okt->contact->config->meta_keywords[$aLanguage['code']]) : '')) ?></p>

				<?php endforeach; ?>
			</fieldset>

			<h4><?php _e('m_contact_seo_map') ?></h4>

			<fieldset>
				<legend><?php _e('c_c_seo_identity_meta') ?></legend>

				<?php foreach ($okt->languages->list as $aLanguage) : ?>
				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_map_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_intitle_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name_map['.$aLanguage['code'].']','p_name_map_'.$aLanguage['code']), 60, 255, (isset($okt->contact->config->name_map[$aLanguage['code']]) ? html::escapeHTML($okt->contact->config->name_map[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_title_tag_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_title_map['.$aLanguage['code'].']','p_title_map_'.$aLanguage['code']), 60, 255, (isset($okt->contact->config->title_map[$aLanguage['code']]) ? html::escapeHTML($okt->contact->config->title_map[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_description_map_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_meta_desc_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_meta_description_map['.$aLanguage['code'].']','p_meta_description_map_'.$aLanguage['code']), 60, 255, (isset($okt->contact->config->meta_description_map[$aLanguage['code']]) ? html::escapeHTML($okt->contact->config->meta_description_map[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_seo_map_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_title_seo_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name_seo_map['.$aLanguage['code'].']','p_name_seo_map_'.$aLanguage['code']), 60, 255, (isset($okt->contact->config->name_seo_map[$aLanguage['code']]) ? html::escapeHTML($okt->contact->config->name_seo_map[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_keywords_map_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_meta_keywords_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::textarea(array('p_meta_keywords_map['.$aLanguage['code'].']','p_meta_keywords_map_'.$aLanguage['code']), 57, 5, (isset($okt->contact->config->meta_keywords_map[$aLanguage['code']]) ? html::escapeHTML($okt->contact->config->meta_keywords_map[$aLanguage['code']]) : '')) ?></p>

				<?php endforeach; ?>
			</fieldset>

		</div><!-- #tab_seo -->

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m','contact'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'config'); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
