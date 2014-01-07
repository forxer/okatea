
<?php # début Okatea : ce template étend le layout
$this->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php # début Okatea : on ajoutent des éléments à l'en-tête HTML
$view['slots']->start('head') ?>

	<?php # début Okatea : on index pas la page ?>
	<meta name="robots" content="none" />
	<?php # fin Okatea : on index pas la page ?>

<?php $view['slots']->stop();
# fin Okatea : on ajoutent des éléments à l'en-tête HTML ?>


<?php # début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(__DIR__.'/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<?php # début Okatea : affichage des éventuelles erreurs
if ($okt->error->notEmpty()) : ?>
	<div class="errors_box">
		<?php echo $okt->error->get(); ?>
	</div>
<?php endif; # fin Okatea : affichage des éventuelles erreurs ?>

<?php # si le mot de passe a été envoyé on l'indique
if ($password_sended) : ?>

<p><?php _e('c_c_auth_email_sent_with_instructions') ?></p>
<p><a href="<?php echo $view->escape(UsersHelpers::getLoginUrl()) ?>"><?php _e('c_c_auth_login') ?></a></p>

<?php # si le mot de passe a été mis à jour on l'indique
elseif ($password_updated) : ?>

<p><?php _e('c_c_auth_password_updated') ?></p>
<p><a href="<?php echo $view->escape(UsersHelpers::getLoginUrl()) ?>"><?php _e('c_c_auth_login') ?></a></p>

<?php # sinon on affiche le formulaire
else : ?>

<form id="forget-password-form" class="userform" action="<?php echo $view->escape(UsersHelpers::getForgetPasswordUrl()) ?>" method="post">

	<p class="field"><label for="email"><?php _e('c_c_auth_give_account_email') ?></label>
	<input id="email" type="text" name="email" maxlength="255" /></p>
	<p class="note"><?php _e('c_c_auth_new_password_link_activate_will_be_sent') ?></p>

	<p><input type="hidden" name="form_sent" value="1" />
	<input class="submit" type="submit" value="<?php _e('c_c_action_Send') ?>" /></p>

	<ul>
		<?php # début Okatea : lien page connexion
		if ($okt->users->config->enable_login_page) : ?>
		<li><a href="<?php echo $view->escape(UsersHelpers::getLoginUrl()) ?>"><?php
		_e('c_c_auth_login') ?></a></li>
		<?php endif; # fin Okatea : lien page connexion ?>

		<?php # début Okatea : lien page inscription
		if ($okt->users->config->enable_register_page) : ?>
		<li><a href="<?php echo $view->escape(UsersHelpers::getRegisterUrl()) ?>"><?php
		_e('c_c_auth_register') ?></a></li>
		<?php endif; # fin Okatea : lien page inscription ?>
	</ul>

</form>

<?php endif ?>
