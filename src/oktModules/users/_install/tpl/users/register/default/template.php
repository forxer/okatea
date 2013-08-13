
<?php # début Okatea : ce template étend le layout
$this->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php # début Okatea : on ajoutent des éléments à l'en-tête HTML
$this->start('head') ?>

	<?php # début Okatea : on index pas la page ?>
	<meta name="robots" content="none" />
	<?php # fin Okatea : on index pas la page ?>

<?php $this->stop();
# fin Okatea : on ajoutent des éléments à l'en-tête HTML ?>


<?php # début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(dirname(__FILE__).'/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : validation JS du formulaire
$aJsValidateRules = new ArrayObject(array(
	array(
		'id' => 'add_username',
		'rules' => array(
			'required: true',
			'minlength: 2',
			'maxlength: 125'
		)
	),
	array(
		'id' => 'add_email',
		'rules' => array(
			'required: true',
			'email: true'
		)
	),
	array(
		'id' => 'add_password',
		'rules' => array(
			'required: true',
			'minlength: 4'
		)
	),
	array(
		'id' => 'add_password_confirm',
		'rules' => array(
			'required: true',
			'equalTo: \'#add_password\''
		)
	)
));

if ($okt->users->config->enable_custom_fields)
{
	while ($rsUserFields->fetch())
	{
		if ($rsUserFields->status != 2) {
			continue;
		}

		$aJsValidateRules[] = array(
			'id' => $rsUserFields->html_id,
			'rules' => array(
				'required: true'
			)
		);
	}
}

$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/validate/jquery.validate.min.js');
$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/validate/additional-methods.min.js');
$okt->page->validate('register-form', $aJsValidateRules);
# fin Okatea : validation JS du formulaire ?>


<?php # début Okatea : message de confirmation de l'inscription
if (!empty($_REQUEST['registered'])) : ?>

		<div class="valid_box">
			<p><?php _e('m_users_confirm_resgitration') ?></p>
		</div>

<?php else : ?>


<?php # début Okatea : affichage des éventuelles erreurs
if ($okt->error->notEmpty()) : ?>
	<div class="error_box">
		<?php echo $okt->error->get(); ?>
	</div>
<?php endif; # fin Okatea : affichage des éventuelles erreurs ?>

<form id="register-form" class="userform" action="<?php echo html::escapeHTML(usersHelpers::getRegisterUrl()) ?>" method="post">

	<fieldset>
		<legend><?php _e('m_users_Account') ?></legend>

		<div class="two-cols">

		<?php # début Okatea : affichage des champs "username" et "email" fusionnés
		if ($okt->users->config->merge_username_email) : ?>
			<p class="field col"><label for="add_email" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_Email') ?></label>
			<?php echo form::text('add_email', 40, 255, html::escapeHTML($aUserRegisterData['email'])) ?></p>
		<?php endif; # fin Okatea : affichage des champs "username" et "email" fusionnés ?>


		<?php # début Okatea : affichage des champs "username" et "email" distincts
		if (!$okt->users->config->merge_username_email) : ?>
			<p class="field col"><label for="add_username" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_user_Username') ?></label>
			<?php echo form::text('add_username', 35, 255, html::escapeHTML($aUserRegisterData['username'])) ?></p>

			<p class="field col"><label for="add_email" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_Email') ?></label>
			<?php echo form::text('add_email', 35, 255, html::escapeHTML($aUserRegisterData['email'])) ?></p>
		<?php endif; # fin Okatea : affichage des champs "username" et "email" distincts ?>

			<?php if ($okt->users->config->user_choose_group) : ?>
			<p class="field col"><label for="add_group_id"><?php _e('c_c_Group') ?></label>
			<?php echo form::select('add_group_id', $aUsersGroups, html::escapeHTML($aUserRegisterData['group_id'])) ?></p>
			<?php endif; ?>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php _e('c_c_user_Password') ?></legend>

		<div class="two-cols">
			<p class="field col"><label for="add_password" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_user_Password') ?></label>
			<?php echo form::password('add_password', 35, 255, html::escapeHTML($aUserRegisterData['password'])) ?></p>

			<p class="field col"><label for="add_password_confirm" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_auth_confirm_password') ?></label>
			<?php echo form::password('add_password_confirm', 35, 255, html::escapeHTML($aUserRegisterData['password_confirm'])) ?></p>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php _e('m_users_Identity') ?></legend>

		<div class="three-cols">
						<p class="field col"><label for="add_civility"><?php _e('c_c_Civility') ?></label>
			<?php echo form::select('add_civility', $aCivilities, html::escapeHTML($aUserRegisterData['civility'])) ?></p>

			<p class="field col"><label for="add_lastname"><?php _e('c_c_Last_name') ?></label>
			<?php echo form::text('add_lastname', 20, 255, html::escapeHTML($aUserRegisterData['lastname'])) ?></p>

			<p class="field col"><label for="add_firstname"><?php _e('c_c_First_name') ?></label>
			<?php echo form::text('add_firstname', 20, 255, html::escapeHTML($aUserRegisterData['firstname'])) ?></p>
		</div>
	</fieldset>

	<?php # -- CORE TRIGGER : adminModUsersRegisterDisplay
	$okt->triggers->callTrigger('adminModUsersRegisterDisplay', $okt); ?>

	<fieldset>
		<legend><?php _e('c_a_menu_localization') ?></legend>

		<div class="two-cols">
			<p class="field col"><label for="add_language"><?php _e('c_c_Language') ?></label>
			<?php echo form::select('add_language', $aLanguages, html::escapeHTML($aUserRegisterData['language'])) ?></p>

			<p class="field col"><label for="add_timezone"><?php _e('c_c_Timezone') ?></label>
			<?php echo form::select('add_timezone', $aTimezone, html::escapeHTML($aUserRegisterData['timezone'])) ?></p>
		</div>
	</fieldset>

	<?php # début Okatea : affichage des champs personnalisés si ils sont activés
	if ($okt->users->config->enable_custom_fields) : ?>
	<div class="two-cols">
		<?php while ($rsUserFields->fetch()) : ?>
			<div class="col">
				<?php echo $rsUserFields->getHtmlField($aPostedData); ?>
			</div>
		<?php endwhile; ?>
	</div>
	<?php endif; # fin Okatea : affichage des champs personnalisés si ils sont activés ?>

	<p><input type="submit" class="submit" value="<?php _e('c_c_auth_register_action') ?>" />
	<?php echo form::hidden('redirect',html::escapeHTML($redirect)) ?>
	<?php echo form::hidden('add_user',1) ?></p>

	<ul>
		<?php # début Okatea : lien page connexion
		if ($okt->users->config->enable_login_page) : ?>
		<li><a href="<?php echo html::escapeHTML(usersHelpers::getLoginUrl()) ?>"><?php
		_e('c_c_auth_login') ?></a></li>
		<?php endif; # fin Okatea : lien page connexion ?>

		<?php # début Okatea : lien page mot de passe oublié
		if ($okt->users->config->enable_forget_password_page) : ?>
		<li><a href="<?php echo html::escapeHTML(usersHelpers::getForgetPasswordUrl()) ?>"><?php
		_e('c_c_auth_forgot_password') ?></a></li>
		<?php endif; # fin Okatea : lien page mot de passe oublié ?>
	</ul>

</form>

<?php endif; ?>
