<?php
/**
 * @ingroup okt_module_users
 * @brief La page de configuration des utilisateurs
 *
 */

use Okatea\Core\Authentification;

# Accès direct interdit
if (!defined('ON_USERS_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Gestionnaires de templates
$oTemplatesForgottenPassword = new Okatea\Themes\TemplatesSet($okt,
	$okt->users->config->templates['forgotten_password'],
	'users/forgotten_password',
	'forgotten_password',
	'module.php?m=users&amp;action=config&amp;'
);

$oTemplatesLogin = new Okatea\Themes\TemplatesSet($okt,
	$okt->users->config->templates['login'],
	'users/login',
	'login',
	'module.php?m=users&amp;action=config&amp;'
);

$oTemplatesLoginRegister = new Okatea\Themes\TemplatesSet($okt,
	$okt->users->config->templates['login_register'],
	'users/login_register',
	'login_register',
	'module.php?m=users&amp;action=config&amp;'
);

$oTemplatesProfile = new Okatea\Themes\TemplatesSet($okt,
	$okt->users->config->templates['profile'],
	'users/profile',
	'profile',
	'module.php?m=users&amp;action=config&amp;'
);

$oTemplatesRegister = new Okatea\Themes\TemplatesSet($okt,
	$okt->users->config->templates['register'],
	'users/register',
	'register',
	'module.php?m=users&amp;action=config&amp;'
);

$oTemplatesUserBar = new Okatea\Themes\TemplatesSet($okt,
	$okt->users->config->templates['user_bar'],
	'users/user_bar',
	'user_bar',
	'module.php?m=users&amp;action=config&amp;'
);


/* Traitements
----------------------------------------------------------*/

if (!empty($_POST['form_sent']))
{
	$p_enable_login_page = !empty($_POST['p_enable_login_page']) ? true : false;
	$p_enable_register_page = !empty($_POST['p_enable_register_page']) ? true : false;
	$p_enable_log_reg_page = !empty($_POST['p_enable_log_reg_page']) ? true : false;
	$p_enable_forget_password_page = !empty($_POST['p_enable_forget_password_page']) ? true : false;
	$p_enable_profile_page = !empty($_POST['p_enable_profile_page']) ? true : false;

	$p_enable_custom_fields = !empty($_POST['p_enable_custom_fields']) ? true : false;

	$p_mail_new_registration = !empty($_POST['p_mail_new_registration']) ? true : false;
	$p_validate_users_registration = !empty($_POST['p_validate_users_registration']) ? true : false;
	$p_merge_username_email = !empty($_POST['p_merge_username_email']) ? true : false;
	$p_auto_log_after_registration = !empty($_POST['p_auto_log_after_registration']) && !$p_validate_users_registration ? true : false;
	$p_user_choose_group = !empty($_POST['p_user_choose_group']) && !$p_validate_users_registration ? true : false;
	$p_default_group = !empty($_POST['p_default_group']) ? intval($_POST['p_default_group']) : 0;

	$p_tpl_forgotten_password = $oTemplatesForgottenPassword->getPostConfig();
	$p_tpl_login = $oTemplatesLogin->getPostConfig();
	$p_tpl_loginRegister = $oTemplatesLoginRegister->getPostConfig();
	$p_tpl_profile = $oTemplatesProfile->getPostConfig();
	$p_tpl_register = $oTemplatesRegister->getPostConfig();
	$p_tpl_user_bar = $oTemplatesUserBar->getPostConfig();

	$p_public_login_url = !empty($_POST['p_public_login_url']) && is_array($_POST['p_public_login_url']) ? $_POST['p_public_login_url'] : array();
	foreach ($p_public_login_url as $lang=>$url) {
		$p_public_login_url[$lang] = util::formatAppPath($url,false,false);
	}
	$p_public_logout_url = !empty($_POST['p_public_logout_url']) && is_array($_POST['p_public_logout_url']) ? $_POST['p_public_logout_url'] : array();
	foreach ($p_public_logout_url as $lang=>$url) {
		$p_public_logout_url[$lang] = util::formatAppPath($url,false,false);
	}
	$p_public_register_url = !empty($_POST['p_public_register_url']) && is_array($_POST['p_public_register_url']) ? $_POST['p_public_register_url'] : array();
	foreach ($p_public_register_url as $lang=>$url) {
		$p_public_register_url[$lang] = util::formatAppPath($url,false,false);
	}
	$p_public_log_reg_url = !empty($_POST['p_public_log_reg_url']) && is_array($_POST['p_public_log_reg_url']) ? $_POST['p_public_log_reg_url'] : array();
	foreach ($p_public_log_reg_url as $lang=>$url) {
		$p_public_log_reg_url[$lang] = util::formatAppPath($url,false,false);
	}
	$p_public_forget_password_url = !empty($_POST['p_public_forget_password_url']) && is_array($_POST['p_public_forget_password_url']) ? $_POST['p_public_forget_password_url'] : array();
	foreach ($p_public_forget_password_url as $lang=>$url) {
		$p_public_forget_password_url[$lang] = util::formatAppPath($url,false,false);
	}
	$p_public_profile_url = !empty($_POST['p_public_profile_url']) && is_array($_POST['p_public_profile_url']) ? $_POST['p_public_profile_url'] : array();
	foreach ($p_public_profile_url as $lang=>$url) {
		$p_public_profile_url[$lang] = util::formatAppPath($url,false,false);
	}

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'enable_custom_fields' 			=> $p_enable_custom_fields,
			'enable_login_page' 			=> $p_enable_login_page,
			'enable_register_page' 			=> $p_enable_register_page,
			'enable_log_reg_page' 			=> $p_enable_log_reg_page,
			'enable_forget_password_page' 	=> $p_enable_forget_password_page,
			'enable_profile_page' 			=> $p_enable_profile_page,

			'mail_new_registration' 		=> $p_mail_new_registration,
			'validate_users_registration' 	=> $p_validate_users_registration,
			'merge_username_email' 			=> $p_merge_username_email,
			'auto_log_after_registration' 	=> $p_auto_log_after_registration,
			'user_choose_group' 			=> $p_user_choose_group,
			'default_group' 				=> $p_default_group,

			'templates' => array(
				'forgotten_password' => $p_tpl_forgotten_password,
				'login' => $p_tpl_login,
				'login_register' => $p_tpl_loginRegister,
				'profile' => $p_tpl_profile,
				'register' => $p_tpl_register,
				'user_bar' => $p_tpl_user_bar
			),

			'public_login_url'				=> $p_public_login_url,
			'public_logout_url'				=> $p_public_logout_url,
			'public_register_url' 			=> $p_public_register_url,
			'public_log_reg_url' 			=> $p_public_log_reg_url,
			'public_forget_password_url'	=> $p_public_forget_password_url,
			'public_profile_url'			=> $p_public_profile_url
		);

		try
		{
			# -- CORE TRIGGER : adminModUsersConfigProcess
			$okt->triggers->callTrigger('adminModUsersConfigProcess', $okt);

			$okt->users->config->write($new_conf);

			$okt->page->flashMessages->addSuccess(__('c_c_confirm_configuration_updated'));

			http::redirect('module.php?m=users&action=config');
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

# liste des groupes
$rsGroups = $okt->users->getGroups();

$groups_array = array();
while ($rsGroups->fetch())
{
	if (!in_array($rsGroups->group_id, array(Authentification::superadmin_group_id,Authentification::admin_group_id,Authentification::guest_group_id))) {
		$groups_array[html::escapeHTML($rsGroups->title)] = $rsGroups->group_id;
	}
}
unset($rsGroups);

# infos page
$okt->page->addGlobalTitle(__('c_a_menu_configuration'));

# Lockable
$okt->page->lockable();

# Onglets
$okt->page->tabs();

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered','.lang-switcher-buttons');
}

# JS pour qu'on ne puissent activer la page de connexion/inscription unifiée
# que si les deux pages connexion ET inscription sont activées
$okt->page->js->addScript('
	function setEnableLogRegStatus() {
		if ($("#p_enable_login_page").is(":checked") && $("#p_enable_register_page").is(":checked")) {
			$("#p_enable_log_reg_page").removeAttr("disabled")
				.parent().removeClass("disabled")
				.parent().find(".note").hide();
		} else {
			$("#p_enable_log_reg_page").attr("disabled", "")
				.parent().addClass("disabled")
				.parent().find(".note").show();
		}
	}

	function handleValidateOptionStatus() {
		if ($("#p_validate_users_registration").is(":checked")) {
			$("#p_user_choose_group,#p_auto_log_after_registration").attr("disabled", "")
				.parent().addClass("disabled");
		}
		else {
			$("#p_user_choose_group,#p_auto_log_after_registration").removeAttr("disabled")
				.parent().removeClass("disabled");
		}
	}
');
$okt->page->js->addReady('
	setEnableLogRegStatus();
	$("#p_enable_login_page,#p_enable_register_page").change(function(){setEnableLogRegStatus();});

	handleValidateOptionStatus();
	$("#p_validate_users_registration").change(function(){handleValidateOptionStatus();});
');


# Construction des onglets
$aEditTabs = new ArrayObject;
$aEditTabs[10] = array(
	'id' => 'tab_general',
	'title' => __('m_users_General'),
	'content' => ''
);

$aEditTabs[10]['content'] =
	'<h3>'.__('m_users_General').'</h3>

	<p class="field"><label>'.form::checkbox('p_enable_custom_fields', 1, $okt->users->config->enable_custom_fields).
	__('m_users_Enable_custom_fields').'</label></p>

	<fieldset>
			<legend>'.__('m_users_Activation_of_public_pages').'</legend>

			<p class="field"><label>'.form::checkbox('p_enable_login_page', 1, $okt->users->config->enable_login_page).
			__('m_users_Enable_login_page').'</label></p>

			<p class="field"><label>'.form::checkbox('p_enable_register_page', 1, $okt->users->config->enable_register_page).
			__('m_users_Enable_registration_page').'</label></p>

			<p class="field"><label>'.form::checkbox('p_enable_log_reg_page', 1, $okt->users->config->enable_log_reg_page, '', '', (!$okt->users->config->enable_login_page || !$okt->users->config->enable_register_page)).
			__('m_users_Enable_log_reg_page').'</label>
			<span class="note">'.__('m_users_Enable_log_reg_page_note').'</span></p>

			<p class="field"><label>'.form::checkbox('p_enable_forget_password_page', 1, $okt->users->config->enable_forget_password_page).
			__('m_users_Enable_page_forgotten_password').'</label></p>

			<p class="field"><label>'.form::checkbox('p_enable_profile_page', 1, $okt->users->config->enable_profile_page).
			__('m_users_Enable_profile_page').'</label></p>

	</fieldset>';


$aEditTabs[20] = array(
	'id' => 'tab_register',
	'title' => __('m_users_Registration'),
	'content' => ''
);

$aEditTabs[20]['content'] =
	'<h3>'.__('m_users_Registration').'</h3>

	<p class="field"><label for="p_mail_new_registration">'.form::checkbox('p_mail_new_registration',1,$okt->users->config->mail_new_registration).
	__('m_users_send_mail_new_registration').'</label></p>

	<p class="field"><label for="p_validate_users_registration">'.form::checkbox('p_validate_users_registration',1,$okt->users->config->validate_users_registration).
	__('m_users_Validation_of_registration_by_administrator').'</label></p>

	<p class="field"><label for="p_merge_username_email">'.form::checkbox('p_merge_username_email',1,$okt->users->config->merge_username_email).
	__('m_users_merge_username_email').'</label></p>

	<p class="field"><label for="p_auto_log_after_registration">'.form::checkbox('p_auto_log_after_registration',1,$okt->users->config->auto_log_after_registration).
	__('m_users_auto_log_after_registration').'</label></p>

	<p class="field"><label for="p_user_choose_group">'.form::checkbox('p_user_choose_group',1,$okt->users->config->user_choose_group).
	__('m_users_Let_users_choose_their_group').'</label></p>

	<p class="field"><label for="p_default_group">'.__('m_users_Default_group').'</label>'.
	form::select('p_default_group', $groups_array, $okt->users->config->default_group).'</p>';


$aEditTabs[30] = array(
	'id' => 'tab_tpl',
	'title' => __('m_users_config_tab_tpl'),
	'content' => ''
);

$aEditTabs[30]['content'] =
	'<h3>'.__('m_users_config_tab_tpl_title').'</h3>

	<h4>'. __('m_users_config_tpl_forgotten_password').'</h4>'.
	$oTemplatesForgottenPassword->getHtmlConfigUsablesTemplates(false).

	'<h4>'. __('m_users_config_tpl_login').'</h4>'.
	$oTemplatesLogin->getHtmlConfigUsablesTemplates(false).

	'<h4>'. __('m_users_config_tpl_login_register').'</h4>'.
	$oTemplatesLoginRegister->getHtmlConfigUsablesTemplates(false).

	'<h4>'. __('m_users_config_tpl_profile').'</h4>'.
	$oTemplatesProfile->getHtmlConfigUsablesTemplates(false).

	'<h4>'. __('m_users_config_tpl_register').'</h4>'.
	$oTemplatesRegister->getHtmlConfigUsablesTemplates(false).

	'<h4>'. __('m_users_config_tpl_user_bar').'</h4>'.
	$oTemplatesUserBar->getHtmlConfigUsablesTemplates(false);


$aEditTabs[40] = array(
	'id' => 'tab_seo',
	'title' => __('c_c_seo'),
	'content' => ''
);

$aEditTabs[40]['content'] =
		'<h3>'.__('c_c_seo_help').'</h3>

		<fieldset>
			<legend>'.__('c_c_seo_schema_url').'</legend>';

		foreach ($okt->languages->list as $aLanguage) :
			$aEditTabs[40]['content'] .=
				'<p class="field" lang="'.$aLanguage['code'].'"><label for="p_public_login_url_'.$aLanguage['code'].'">'.sprintf(__('m_users_URL_of_login_page_from_%s'), '<code>'.$okt->config->app_url.'</code>').'<span class="lang-switcher-buttons"></span></label>'.
				form::text(array('p_public_login_url['.$aLanguage['code'].']','p_public_login_url_'.$aLanguage['code']), 40, 255, (isset($okt->users->config->public_login_url[$aLanguage['code']]) ? html::escapeHTML($okt->users->config->public_login_url[$aLanguage['code']]) : '')).'</p>

				<p class="field" lang="'.$aLanguage['code'].'"><label for="p_public_logout_url_'.$aLanguage['code'].'">'.sprintf(__('m_users_URL_of_disconnection_page_from_%s'), '<code>'.$okt->config->app_url.'</code>').'<span class="lang-switcher-buttons"></span></label>'.
				form::text(array('p_public_logout_url['.$aLanguage['code'].']','p_public_logout_url_'.$aLanguage['code']), 40, 255, (isset($okt->users->config->public_logout_url[$aLanguage['code']]) ? html::escapeHTML($okt->users->config->public_logout_url[$aLanguage['code']]) : '')).'</p>

				<p class="field" lang="'.$aLanguage['code'].'"><label for="p_public_register_url_'.$aLanguage['code'].'">'.sprintf(__('m_users_URL_of_registration_page_from_%s'), '<code>'.$okt->config->app_url.'</code>').'<span class="lang-switcher-buttons"></span></label>'.
				form::text(array('p_public_register_url['.$aLanguage['code'].']','p_public_register_url_'.$aLanguage['code']), 40, 255, (isset($okt->users->config->public_register_url[$aLanguage['code']]) ? html::escapeHTML($okt->users->config->public_register_url[$aLanguage['code']]) : '')).'</p>

				<p class="field" lang="'.$aLanguage['code'].'"><label for="p_public_log_reg_url_'.$aLanguage['code'].'">'.sprintf(__('m_users_URL_of_log_reg_page_from_%s'), '<code>'.$okt->config->app_url.'</code>').'<span class="lang-switcher-buttons"></span></label>'.
				form::text(array('p_public_log_reg_url['.$aLanguage['code'].']','p_public_log_reg_url_'.$aLanguage['code']), 40, 255, (isset($okt->users->config->public_log_reg_url[$aLanguage['code']]) ? html::escapeHTML($okt->users->config->public_log_reg_url[$aLanguage['code']]) : '')).'</p>

				<p class="field" lang="'.$aLanguage['code'].'"><label for="p_public_forget_password_url_'.$aLanguage['code'].'">'.sprintf(__('m_users_URL_of_forgotten_password_page_from_%s'), '<code>'.$okt->config->app_url.'</code>').'<span class="lang-switcher-buttons"></span></label>'.
				form::text(array('p_public_forget_password_url['.$aLanguage['code'].']','p_public_forget_password_url_'.$aLanguage['code']), 40, 255, (isset($okt->users->config->public_forget_password_url[$aLanguage['code']]) ? html::escapeHTML($okt->users->config->public_forget_password_url[$aLanguage['code']]) : '')).'</p>

				<p class="field" lang="'.$aLanguage['code'].'"><label for="p_public_profile_url_'.$aLanguage['code'].'">'.sprintf(__('m_users_URL_of_user_profile_page_from_%s'), '<code>'.$okt->config->app_url.'</code>').'<span class="lang-switcher-buttons"></span></label>'.
				form::text(array('p_public_profile_url['.$aLanguage['code'].']','p_public_profile_url_'.$aLanguage['code']), 40, 255, (isset($okt->users->config->public_profile_url[$aLanguage['code']]) ? html::escapeHTML($okt->users->config->public_profile_url[$aLanguage['code']]) : '')).'</p>';
		endforeach;
		$aEditTabs[40]['content'] .= '</fieldset>';


# -- CORE TRIGGER : adminModUsersEditDisplayTabs
$okt->triggers->callTrigger('adminModUsersConfigTabs', $okt, $aEditTabs);

$aEditTabs->ksort();

# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post">
	<div id="tabered">
		<ul>
		<?php foreach ($aEditTabs as $aTabInfos) : ?>
			<li><a href="#<?php echo $aTabInfos['id'] ?>"><span><?php echo $aTabInfos['title'] ?></span></a></li>
		<?php endforeach; ?>
		</ul>

		<?php foreach ($aEditTabs as $sTabUrl=>$aTabInfos) : ?>
		<div id="<?php echo $aTabInfos['id'] ?>">
			<?php echo $aTabInfos['content'] ?>
		</div><!-- #<?php echo $aTabInfos['id'] ?> -->
		<?php endforeach; ?>

	</div><!-- #tabered -->

	<p><?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('m'), 'users'); ?>
	<?php echo form::hidden(array('action'), 'config'); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_Save') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>

