<?php
/**
 * @ingroup okt_module_users
 * @brief La page profil utilisateur courant
 *
 */


# Accès direct interdit
if (!defined('ON_USERS_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# récupération des infos utilisateur
$user_id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : null;

if ($user_id === null || $user_id != $okt->user->id) {
	http::redirect('index.php');
}

$rsUser = $okt->users->getUser($user_id);

$edit_username = $rsUser->username;
$edit_email = $rsUser->email;
$edit_civility = $rsUser->civility;
$edit_lastname = $rsUser->lastname;
$edit_firstname = $rsUser->firstname;
$edit_language = $rsUser->language;
$edit_timezone = $rsUser->timezone;

$edit_password = '';
$edit_password_confirm = '';

$sUserCN = oktAuth::getUserCN($rsUser->username,$rsUser->lastname,$rsUser->firstname);
unset($rsUser);


/* Traitements
----------------------------------------------------------*/

# Suppression des cookies
if (!empty($_REQUEST['delete_cookies']))
{
	$aCookies = array_keys($_COOKIE);
	unset($aCookies[OKT_COOKIE_AUTH_NAME]);

	foreach ($aCookies as $c)
	{
		unset($_COOKIE[$c]);
		setcookie($c,null);
	}

	$okt->page->flashMessages->addSuccess(__('m_users_cookies_has_been_deleted'));

	http::redirect('module.php?m=users&action=profil&id='.$user_id);
}

# Formulaire de changement de mot de passe
if (!empty($_POST['change_password']) && $okt->checkPerm('change_password'))
{
	$upd_params = array(
		'id' => $user_id
	);

	$upd_params['password'] = !empty($_POST['edit_password']) ? $_POST['edit_password'] : '';
	$upd_params['password_confirm'] = !empty($_POST['edit_password_confirm']) ? $_POST['edit_password_confirm'] : '';

	$okt->users->changeUserPassword($upd_params);

	$okt->page->flashMessages->addSuccess(__('m_users_profile_edited'));

	http::redirect('module.php?m=users&action=profil&id='.$user_id);
}

# Formulaire de modification de l'utilisateur envoyé
if (!empty($_POST['form_sent']))
{
	$edit_civility = isset($_POST['edit_civility']) ? intval($_POST['edit_civility']) : 0;
	$edit_username = isset($_POST['edit_username']) ? $_POST['edit_username'] : '';
	$edit_email = isset($_POST['edit_email']) ? $_POST['edit_email'] : '';
	$edit_lastname = isset($_POST['edit_lastname']) ? $_POST['edit_lastname'] : '';
	$edit_firstname = isset($_POST['edit_firstname']) ? $_POST['edit_firstname'] : '';
	$edit_language = isset($_POST['edit_language']) ? $_POST['edit_language'] : '';
	$edit_timezone = isset($_POST['edit_timezone']) ? $_POST['edit_timezone'] : '';

	$upd_params = array(
		'id' => $user_id,
		'civility' => $edit_civility,
		'username' => $edit_username,
		'email' => $edit_email,
		'lastname' => $edit_lastname,
		'firstname' => $edit_firstname,
		'language' => $edit_language,
		'timezone' => $edit_timezone
	);

	$okt->users->updUser($upd_params);

	$okt->page->flashMessages->addSuccess(__('m_users_profile_edited'));

	http::redirect('module.php?m=users&action=profil&id='.$user_id);
}


/* Affichage
----------------------------------------------------------*/

# langues
$rs = $okt->languages->getLanguages();
$aLanguages = array();
while ($rs->fetch()) {
	$aLanguages[html::escapeHTML($rs->title)] = $rs->code;
}

# civilités
$aCivilities = array_merge(
	array('&nbsp;'=>0),
	module_users::getCivilities(true)
);


# Titre de la page
$okt->page->addGlobalTitle(html::escapeHTML($sUserCN));

# Tabs
$okt->page->tabs();

# Validation javascript
$okt->page->validate('edit-user-form',array(
	array(
		'id' => 'edit_username',
		'rules' => array(
			'required: true',
			'minlength: 2',
			'maxlength: 125'
		)
	),
	array(
		'id' => 'edit_email',
		'rules' => array(
			'required: true',
			'email: true'
		)
	)
));

$iNumCookies = count($_COOKIE);


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<div id="tabered">
	<ul>
		<li><a href="#tab-user-profil"><span><?php echo html::escapeHTML($edit_username) ?></span></a></li>
		<li><a href="#tab-edit-user"><span><?php _e('c_c_action_Edit')?></span></a></li>
		<?php if ($okt->checkPerm('change_password')) : ?>
		<li><a href="#tab-change-password"><span><?php _e('c_c_user_Password')?></span></a></li>
		<?php endif; ?>
	</ul>

	<div id="tab-user-profil">

		<p><?php printf(__('c_c_user_hello_%s'), html::escapeHTML($sUserCN)) ?>.</p>

		<p><?php printf(__('c_c_user_last_visit_on_%s'), dt::str('%A %d %B %Y %H:%M',$okt->user->last_visit)); ?>.</p>

		<p><?php printf(__('m_users_%s_cookies_registered'), $iNumCookies)?> - <a href="module.php?m=users&amp;action=profil&amp;id=<?php echo $user_id ?>&amp;delete_cookies=1"><?php _e('m_users_delete_cookies')?></a></p>

		<p><a href="?logout=1"><?php _e('c_c_user_Log_off_action') ?></a></p>

	</div><!-- #tab-user-profil -->

	<div id="tab-edit-user">
		<form id="edit-user-form" action="module.php" method="post">
			<fieldset>
				<legend><?php _e('m_users_Identity')?></legend>

				<div class="two-cols">
					<p class="field col"><label for="edit_username" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_user_Username')?></label>
					<?php echo form::text('edit_username', 40, 255, html::escapeHTML($edit_username)) ?></p>

					<p class="field col"><label for="edit_email" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_Email')?></label>
					<?php echo form::text('edit_email', 40, 255, html::escapeHTML($edit_email)) ?></p>
				</div>

				<div class="three-cols">
					<p class="field col"><label for="edit_civility"><?php _e('c_c_Civility') ?></label>
					<?php echo form::select('edit_civility', $aCivilities, $edit_civility) ?></p>

					<p class="field col"><label for="edit_lastname"><?php _e('c_c_Last_name')?></label>
					<?php echo form::text('edit_lastname', 40, 255, html::escapeHTML($edit_lastname)) ?></p>

					<p class="field col"><label for="edit_firstname"><?php _e('c_c_First_name')?></label>
					<?php echo form::text('edit_firstname', 40, 255, html::escapeHTML($edit_firstname)) ?></p>
				</div>
			</fieldset>

			<fieldset>
				<legend><?php _e('c_a_menu_localization')?></legend>
				<div class="two-cols">
					<p class="field col"><label for="edit_language"><?php _e('c_c_Language')?></label>
					<?php echo form::select('edit_language', $aLanguages, html::escapeHTML($edit_language)) ?></p>

					<p class="field col"><label for="edit_timezone"><?php _e('c_c_Timezone')?></label>
					<?php echo form::select('edit_timezone', dt::getZones(true,true), html::escapeHTML($edit_timezone)) ?></p>
				</div>
			</fieldset>


			<p><?php echo form::hidden('m','users') ?>
			<?php echo form::hidden('action','profil') ?>
			<?php echo form::hidden('form_sent',1) ?>
			<?php echo form::hidden('id',$user_id) ?>
			<?php echo adminPage::formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_Edit') ?>" /></p>
		</form>
	</div><!-- #tab-edit-user -->

	<?php if ($okt->checkPerm('change_password')) : ?>
	<div id="tab-change-password">
		<h3><?php _e('c_c_user_Password')?></h3>
		<form id="change-password-form" action="module.php" method="post">
			<fieldset>
				<legend><?php _e('m_users_Edit_password')?></legend>
				<div class="two-cols">
					<p class="field col"><label for="edit_password"><?php _e('c_c_user_Password')?></label>
					<?php echo form::password('edit_password', 40, 255, html::escapeHTML($edit_password)) ?></p>

					<p class="field col"><label for="edit_password_confirm"><?php _e('c_c_auth_confirm_password')?></label>
					<?php echo form::password('edit_password_confirm', 40, 255, html::escapeHTML($edit_password_confirm)) ?></p>
				</div>
				<p class="note"><?php _e('m_users_Warning_password_changement')?></p>
			</fieldset>
			<p><?php echo form::hidden('m','users') ?>
			<?php echo form::hidden('action','profil') ?>
			<?php echo form::hidden('change_password',1) ?>
			<?php echo form::hidden('id',$user_id) ?>
			<?php echo adminPage::formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_Edit') ?>" /></p>
		</form>
	</div><!-- #tab-change-password -->
	<?php endif; ?>

</div><!-- #tabered -->

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
