

<?php use Okatea\Tao\Forms\Statics\FormElements as form; ?>

<?php 
# début Okatea : ce template étend le layout
$view->extend('Layout');
# fin Okatea : ce template étend le layout ?>


<?php 
# début Okatea : on ajoutent des éléments à l'en-tête HTML
$view['slots']->start('head')?>

	<?php # début Okatea : on index pas la page ?>
<meta name="robots" content="none" />
<?php # fin Okatea : on index pas la page ?>

<?php

$view['slots']->stop();
# fin Okatea : on ajoutent des éléments à l'en-tête HTML ?>


<?php 
# début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(__DIR__ . '/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<?php 
# début Okatea : ajout de jQuery
$okt->page->js->addFile($okt['public_url'] . '/components/jquery/dist/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php 
# début Okatea : validation JS du formulaire
$aJsValidateRules = new ArrayObject(array(
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

if ($okt->users->config->enable_custom_fields)
{
	while ($rsUserFields->fetch())
	{
		if ($rsUserFields->status != 2)
		{
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

$okt->page->js->addFile($okt['public_url'] . '/js/jquery/validate/jquery.validate.min.js');
$okt->page->js->addFile($okt['public_url'] . '/js/jquery/validate/additional-methods.min.js');
$okt->page->validate('edit-user-form', $aJsValidateRules);
# fin Okatea : validation JS du formulaire ?>


<?php 
# début Okatea : affichage des éventuelles erreurs
if ($okt->error->notEmpty())
:
	?>
<div class="errors_box">
		<?php echo $okt->error->get(); ?>
	</div>
<?php endif; # fin Okatea : affichage des éventuelles erreurs ?>



<?php 
# début Okatea : affichage des champs personnalisés non-modifiables par l'utilisateur
if ($okt->users->config->enable_custom_fields)
:
	?>
<div id="user-infos">
	<div class="two-cols">
	<?php while ($rsAdminFields->fetch()) : ?>
		<p class="col">
			<strong><?php echo $view->escape($rsAdminFields->title); ?> : </strong>
			<em><?php if (isset($aFieldsValues[$rsAdminFields->id])) echo $view->escape($aFieldsValues[$rsAdminFields->id]); ?></em>
		</p>
	<?php endwhile; ?>
	</div>
</div>
<?php endif; # fin Okatea : affichage des champs personnalisés non-modifiables par l'utilisateur ?>


<h2><?php _e('m_users_Update_user_profile') ?></h2>

<form id="edit-user-form" class="userform"
	action="<?php echo $view->generateUrl('usersProfile') ?>" method="post">
	<fieldset>
		<legend><?php _e('m_users_Identity') ?></legend>

		<div class="two-cols">

		<?php 
# début Okatea : affichage des champs "username" et "email" fusionnés
		if ($okt['config']->users_registration['merge_username_email'])
		:
			?>
			<p class="field col">
				<label for="edit_email" title="<?php _e('c_c_required_field') ?>"
					class="required">Email</label>
			<?php echo form::text('edit_email', 40, 255, $view->escape($aUserProfilData['email'])) ?></p>
		<?php endif; # fin Okatea : affichage des champs "username" et "email" fusionnés ?>

		<?php 
# début Okatea : affichage des champs "username" et "email" distincts
		if (!$okt['config']->users_registration['merge_username_email'])
		:
			?>
			<p class="field col">
				<label for="edit_username" title="<?php _e('c_c_required_field') ?>"
					class="required">Nom d'utilisateur</label>
			<?php echo form::text('edit_username', 35, 255, $view->escape($aUserProfilData['username'])) ?></p>

			<p class="field col">
				<label for="edit_email" title="<?php _e('c_c_required_field') ?>"
					class="required">Email</label>
			<?php echo form::text('edit_email', 35, 255, $view->escape($aUserProfilData['email'])) ?></p>
		<?php endif; # fin Okatea : affichage des champs "username" et "email" distincts ?>
		</div>

		<div class="three-cols">
			<p class="field col">
				<label for="edit_civility"><?php _e('c_c_Civility') ?></label>
			<?php echo form::select('edit_civility', $aCivilities, $view->escape($aUserProfilData['civility'])) ?></p>

			<p class="field col">
				<label for="edit_lastname"><?php _e('c_c_Last_name') ?></label>
			<?php echo form::text('edit_lastname', 20, 255, $view->escape($aUserProfilData['lastname'])) ?></p>

			<p class="field col">
				<label for="edit_firstname"><?php _e('c_c_First_name') ?></label>
			<?php echo form::text('edit_firstname', 20, 255, $view->escape($aUserProfilData['firstname'])) ?></p>
		</div>
	</fieldset>

	<?php 
# -- CORE TRIGGER : adminModUsersProfileDisplay
	$okt['triggers']->callTrigger('adminModUsersProfileDisplay');
	?>

	<fieldset>
		<legend><?php _e('c_a_menu_localization') ?></legend>
		<div class="two-cols">
			<p class="field col">
				<label for="edit_language"><?php _e('c_c_Language') ?></label>
			<?php echo form::select('edit_language', $aLanguages, $view->escape($aUserProfilData['language'])) ?></p>

			<p class="field col">
				<label for="edit_timezone"><?php _e('c_c_Timezone') ?></label>
			<?php echo form::select('edit_timezone', $aTimezone, $view->escape($aUserProfilData['timezone'])) ?></p>
		</div>
	</fieldset>

	<?php 
# début Okatea : affichage des champs personnalisés si ils sont activés
	if ($okt->users->config->enable_custom_fields)
	:
		?>
	<div class="two-cols">
		<?php while ($rsUserFields->fetch()) : ?>
			<div class="col">
				<?php echo $rsUserFields->getHtmlField($aPostedData); ?>
			</div>
		<?php endwhile; ?>
	</div>
	<?php endif; # fin Okatea : affichage des champs personnalisés si ils sont activés ?>

	<p>
		<input type="submit" value="<?php _e('c_c_action_Edit') ?>" />
	<?php echo form::hidden('form_sent', 1) ?></p>
</form>

<?php if ($okt['visitor']->checkPerm('change_password')) : ?>
<h2><?php _e('m_users_Update_paswword') ?></h2>
<form class="userform" id="change-password-form"
	action="<?php echo $view->generateUrl('usersProfile') ?>" method="post">
	<fieldset>
		<legend><?php _e('m_users_Update_paswword') ?></legend>
		<div class="two-cols">
			<p class="field col">
				<label for="edit_password"><?php _e('c_c_user_Password') ?></label>
			<?php echo form::password('edit_password', 35, 255, $view->escape($aUserProfilData['password'])) ?></p>

			<p class="field col">
				<label for="edit_password_confirm"><?php _e('c_c_auth_confirm_password') ?></label>
			<?php echo form::password('edit_password_confirm', 35, 255, $view->escape($aUserProfilData['password_confirm'])) ?></p>
		</div>
		<p class="note"><?php _e('m_users_Note_password') ?></p>
	</fieldset>

	<p>
		<input type="submit" value="<?php _e('c_c_action_Edit') ?>" />
	<?php echo form::hidden('change_password', 1) ?></p>
</form>
<?php endif; ?>
