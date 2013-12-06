
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


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile(OKT_PUBLIC_URL.'/js/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : validation JS du formulaire
$okt->page->validateForm();
$okt->page->js->addReady("
	var validator = $('#login-form').validate({
		rules: {
			user_id: {
				required: true,
				minlength: 2
			},
			user_pwd: {
				required: true,
				minlength: 4
			}
		}
	});
");
# fin Okatea : validation JS du formulaire ?>


<?php # début Okatea : affichage des éventuelles erreurs
if ($okt->error->notEmpty()) : ?>
	<div class="error_box">
		<?php echo $okt->error->get(); ?>
	</div>
<?php endif; # fin Okatea : affichage des éventuelles erreurs ?>


<form id="login-form" class="userform" action="<?php echo html::escapeHTML(usersHelpers::getLoginUrl()) ?>" method="post">

	<p class="field"><label for="user_id"><?php if ($okt->users->config->merge_username_email) { _e('c_c_Email'); } else { _e('c_c_user_Username'); } ?></label>
	<input name="user_id" id="user_id" type="text" maxlength="225" value="<?php echo html::escapeHTML($user_id) ?>" /></p>

	<p class="field"><label for="user_pwd"><?php _e('c_c_user_Password') ?></label>
	<input name="user_pwd" id="user_pwd" type="password" maxlength="225" /></p>

	<p><input type="checkbox" id="user_remember" name="user_remember" value="1" />
	<label for="user_remember"><?php _e('c_c_auth_remember_me') ?></label></p>

	<p><input type="hidden" name="redirect" value="<?php echo rawurlencode($redirect) ?>" />
	<input type="hidden" name="sended" value="1" />
	<input class="submit btn" type="submit" value="<?php _e('c_c_auth_login_action') ?>" /></p>

	<p class="note"><?php _e('c_c_auth_must_accept_cookies_private_area') ?></p>

	<ul>
		<?php # début Okatea : lien page mot de passe oublié
		if ($okt->users->config->enable_forget_password_page) : ?>
		<li><a href="<?php echo html::escapeHTML(usersHelpers::getForgetPasswordUrl()) ?>"><?php
		_e('c_c_auth_forgot_password') ?></a></li>
		<?php endif; # fin Okatea : lien page mot de passe oublié ?>

		<?php # début Okatea : lien page inscription
		if ($okt->users->config->enable_register_page) : ?>
		<li><a href="<?php echo html::escapeHTML(usersHelpers::getRegisterUrl()) ?>"><?php
		_e('c_c_auth_register') ?></a></li>
		<?php endif; # fin Okatea : lien page inscription ?>
	</ul>

</form>
