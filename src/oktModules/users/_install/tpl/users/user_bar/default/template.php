
<?php # début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(dirname(__FILE__).'/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<div id="userbar">
<?php if (!$okt->user->infos->is_guest) : ?>

	<p>
		<?php printf(__('c_c_user_hello_%s'), html::escapeHTML(oktAuth::getUserCN($okt->user->username, $okt->user->lastname, $okt->user->firstname))) ?>

		<?php # début Okatea : lien page profil
		if ($okt->users->config->enable_profile_page) : ?>
		- <a href="<?php echo html::escapeHTML(usersHelpers::getProfileUrl()) ?>"><?php _e('c_c_user_profile') ?></a>
		<?php endif; # fin Okatea : lien page profil ?>

		<?php # début Okatea : lien déconnexion ?>
		- <a href="<?php echo html::escapeHTML(usersHelpers::getLogoutUrl()) ?>"><?php
		_e('c_c_user_Log_off_action') ?></a>
		<?php # fin Okatea : lien déconnexion ?>
	</p>

	<?php # début Okatea : date de dernière visite ?>
	<p><?php printf(__('c_c_user_last_visit_on_%s'), dt::str(__('%A, %B %d, %Y, %H:%M'), $okt->user->last_visit)); ?></p>
	<?php # fin Okatea : date de dernière visite ?>

<?php else : ?>

	<p>
		<?php _e('c_c_user_hello_you_are_not_logged') ?>

		<?php # début Okatea : lien page connexion
		if ($okt->users->config->enable_login_page) : ?>
		- <a href="<?php echo html::escapeHTML(usersHelpers::getLoginUrl($okt->config->self_uri)) ?>"><?php
		_e('c_c_auth_login') ?></a>
		<?php endif; # fin Okatea : lien page connexion ?>

		<?php # début Okatea : lien page inscription
		if ($okt->users->config->enable_register_page) : ?>
		- <a href="<?php echo html::escapeHTML(usersHelpers::getRegisterUrl()) ?>"><?php
		_e('c_c_auth_register') ?></a>
		<?php endif; # fin Okatea : lien page inscription ?>

		<?php # début Okatea : lien page mot de passe oublié
		if ($okt->users->config->enable_forget_password_page) : ?>
		- <a href="<?php echo html::escapeHTML(usersHelpers::getForgetPasswordUrl()) ?>"><?php
		_e('c_c_auth_forgot_password') ?></a>
		<?php endif; # fin Okatea : lien page mot de passe oublié ?>
	</p>

<?php endif; ?>
</div><!-- #userbar -->
