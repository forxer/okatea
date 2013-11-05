<?php
/**
 * @ingroup okt_module_guestbook
 * @brief La page de configuration du module.
 *
 */


# Accès direct interdit
if (!defined('ON_GUESTBOOK_MODULE')) die;

$p_chp_language = $okt->guestbook->config->chp_language;
$p_chp_nom = $okt->guestbook->config->chp_nom;
$p_chp_mail = $okt->guestbook->config->chp_mail;
$p_chp_url = $okt->guestbook->config->chp_url;
$p_chp_note = $okt->guestbook->config->chp_note;

$p_validation = $okt->guestbook->config->validation;
$p_emails_list = $okt->guestbook->config->emails_list;
$p_autodelete_spam = $okt->guestbook->config->autodelete_spam;


if (!empty($_POST['form_sent']))
{
	$p_chp_language = intval($_POST['p_chp_language']);
	$p_chp_nom = intval($_POST['p_chp_nom']);
	$p_chp_mail = intval($_POST['p_chp_mail']);
	$p_chp_url = intval($_POST['p_chp_url']);
	$p_chp_note = intval($_POST['p_chp_note']);

	$p_validation = intval($_POST['p_validation']);
	$p_emails_list = $_POST['p_emails_list'];
	$p_autodelete_spam = intval($_POST['p_autodelete_spam']);

	if (!preg_match('/^[0-9]+$/',$p_autodelete_spam)) {
		$okt->error->set(__('m_guestbook_you_must_enter_a_valid_number'));
	}

	$p_emails_list = explode(',',$p_emails_list);
	foreach ($p_emails_list as $i=>$mail)
	{
		$mail = trim($mail);
		if ($mail != '' && !text::isEmail($mail)) {
			$okt->error->set(sprintf(__('m_guestbook_address_%s_is_invalid'), $mail));
		}
		$p_emails_list[$i] = $mail;
	}
	$p_emails_list = implode(',',$p_emails_list);

	$p_captcha = !empty($_POST['p_captcha']) ? $_POST['p_captcha'] : '';

	$p_name = !empty($_POST['p_name']) && is_array($_POST['p_name'])  ? $_POST['p_name'] : array();
	$p_name_seo = !empty($_POST['p_name_seo']) && is_array($_POST['p_name_seo'])  ? $_POST['p_name_seo'] : array();
	$p_title = !empty($_POST['p_title']) && is_array($_POST['p_title']) ? $_POST['p_title'] : array();
	$p_meta_description = !empty($_POST['p_meta_description']) && is_array($_POST['p_meta_description']) ? $_POST['p_meta_description'] : array();
	$p_meta_keywords = !empty($_POST['p_meta_keywords']) && is_array($_POST['p_meta_keywords']) ? $_POST['p_meta_keywords'] : array();

	$p_public_url = !empty($_POST['p_public_url']) ? $_POST['p_public_url'] : '';

	foreach ($p_public_url as $lang=>$url) {
		$p_public_url[$lang] = util::formatAppPath($url,false,false);
	}

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'chp_language' => (integer)$p_chp_language,
			'chp_nom' => (integer)$p_chp_nom,
			'chp_mail' => (integer)$p_chp_mail,
			'chp_url' => (integer)$p_chp_url,
			'chp_note' => (integer)$p_chp_note,

			'validation' => (integer)$p_validation,
			'emails_list' => $p_emails_list,

			'autodelete_spam' => (integer)$p_autodelete_spam,

			'captcha' => $p_captcha,

			'name' => $p_name,
			'name_seo' => $p_name_seo,
			'title' => $p_title,
			'meta_description' => $p_meta_description,
			'meta_keywords' => $p_meta_keywords,

			'public_url' => $p_public_url
		);

		try
		{
			$okt->guestbook->config->write($new_conf);

			$okt->page->flashMessages->addSuccess(__('c_c_confirm_configuration_updated'));

			$okt->redirect('module.php?m=guestbook&action=config');
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
$okt->page->addGlobalTitle(__('c_a_menu_configuration'));

# Lockable
$okt->page->lockable();

# Tabs
$okt->page->tabs();

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered','.lang-switcher-buttons');
}

$aFieldChoices = util::getStatusFieldChoices();

$aLanguageFieldChoices = util::getStatusFieldChoices(false);

# En-tête
include OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post">
	<div id="tabered">
		<ul>
			<li><a href="#tab-fields"><?php _e('c_c_fields')?></a></li>
			<li><a href="#tab-advanced"><?php _e('c_a_menu_advanced')?></a></li>
			<li><a href="#tab_seo"><span><?php _e('c_c_seo')?></span></a></li>
		</ul>
		<div id="tab-fields">
			<fieldset>
				<legend><?php _e('m_guestbook_activation_of_various_fields')?></legend>

				<div class="three-cols">
					<p class="col field"><label for="p_chp_nom"><?php _e('c_c_Name')?></label>
					<?php echo form::select('p_chp_nom', $aFieldChoices, $p_chp_nom) ?></p>

					<p class="col field"><label for="p_chp_mail"><?php _e('c_c_Email')?></label>
					<?php echo form::select('p_chp_mail', $aFieldChoices, $p_chp_mail) ?></p>

					<p class="col field"><label for="p_chp_language"><?php _e('c_c_Language')?></label>
					<?php echo form::select('p_chp_language', $aLanguageFieldChoices, $p_chp_language) ?></p>
				</div>
				<div class="three-cols">
					<p class="col field"><label for="p_chp_url"><abbr title="Uniform Resource Locator">URL</abbr></label>
					<?php echo form::select('p_chp_url', $aFieldChoices, $p_chp_url) ?></p>

					<p class="col field"><label for="p_chp_note"><?php _e('m_guestbook_note')?></label>
					<?php echo form::select('p_chp_note', $aFieldChoices, $p_chp_note) ?></p>
				</div>
			</fieldset>
		</div><!-- #tab-fields -->

		<div id="tab-advanced">
			<?php if ($okt->page->hasCaptcha()) : ?>
				<p class="field"><label for="p_captcha"><?php _e('m_guestbook_Captcha')?></label>
				<?php echo form::select('p_captcha',array_merge(array(__('c_c_Disabled')=>0),$okt->page->getCaptchaList(true)),$okt->guestbook->config->captcha) ?></p>
			<?php else : ?>
				<p><?php _e('m_guestbook_no_available_captcha')?>
				<?php echo form::hidden('p_captcha',0); ?></p>
			<?php endif;?>

			<p class="field"><span class="fake-label"><?php _e('m_guestbook_Validation_before_publication')?></span>
			<label><?php echo form::radio(array('p_validation'), 1, $p_validation) ?> <?php _e('c_c_Yes')?></label>
			<label><?php echo form::radio(array('p_validation'), 0, !$p_validation) ?> <?php _e('c_c_No')?></label></p>

			<p class="field"><label for="p_emails_list"><?php _e('m_guestbook_Addresses_separated_by_a_comma')?></label>
			<?php echo form::textarea('p_emails_list', 57, 3, $p_emails_list) ?></p>

			<p class="field"><label for="p_autodelete_spam">suppression automatique du SPAM au bout de combien de jour ? (0 pour désactiver)</label>
			<?php echo form::text('p_autodelete_spam', 3, 5, $p_autodelete_spam) ?></p>
		</div><!-- #tab-advanced -->

		<div id="tab_seo">
			<h3><?php _e('c_c_seo_help') ?></h3>
			<fieldset>
				<legend><?php _e('c_c_seo_identity_meta')?></legend>
				<?php foreach ($okt->languages->list as $aLanguage) :?>
				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_<?php echo $aLanguage['code'] ?>"><?php _e('c_c_seo_module_intitle') ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name['.$aLanguage['code'].']','p_name_'.$aLanguage['code']), 40, 255, (isset($okt->guestbook->config->name[$aLanguage['code']]) ? html::escapeHTML($okt->guestbook->config->name[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>"><?php _e('c_c_seo_module_title_tag') ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 40, 255, (isset($okt->guestbook->config->title[$aLanguage['code']]) ? html::escapeHTML($okt->guestbook->config->title[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php _e('c_c_seo_meta_desc')?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, (isset($okt->guestbook->config->meta_description[$aLanguage['code']]) ? html::escapeHTML($okt->guestbook->config->meta_description[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_seo_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_title_seo_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name_seo['.$aLanguage['code'].']','p_name_seo_'.$aLanguage['code']), 60, 255, (isset($okt->guestbook->config->name_seo[$aLanguage['code']]) ? html::escapeHTML($okt->guestbook->config->name_seo[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php _e('c_c_seo_meta_keywords')?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 57, 5, (isset($okt->guestbook->config->meta_keywords[$aLanguage['code']]) ? html::escapeHTML($okt->guestbook->config->meta_keywords[$aLanguage['code']]) : '')) ?></p>
				<?php endforeach;?>
			</fieldset>

			<fieldset>
				<legend><?php _e('c_c_seo_schema_url')?></legend>
				<?php foreach ($okt->languages->list as $aLanguage) :?>
				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_public_url"><?php _e('m_guestbook_Guestbook_URL_from')?> <code><?php echo $okt->config->app_url ?></code><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_public_url['.$aLanguage['code'].']','p_public_url_'.$aLanguage['code']), 40, 255, html::escapeHTML(isset($okt->guestbook->config->public_url[$aLanguage['code']]) ? $okt->guestbook->config->public_url[$aLanguage['code']] : '')) ?></p>
				<?php endforeach;?>
			</fieldset>

		</div><!-- #tab_seo -->

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m', 'guestbook'); ?>
	<?php echo form::hidden('action', 'config'); ?>
	<?php echo form::hidden('form_sent', 1); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_Save')?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>

