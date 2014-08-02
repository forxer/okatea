<?php use Okatea\Tao\L10n\DateTime; ?>


<?php
# début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(__DIR__ . '/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<div id="userbar">
<?php if (!$okt->user->infos->is_guest) : ?>

	<p>
		<?php printf(__('c_c_user_hello_%s'), $view->escape($okt->user->usedname))?>

		<?php # début Okatea : lien page profil
		if ($okt['config']->users['pages']['profile']) : ?>
		- <a href="<?php echo $view->generateUrl('usersProfile') ?>"><?php _e('c_c_user_profile') ?></a>
		<?php endif; # fin Okatea : lien page profil ?>

		<?php # début Okatea : lien déconnexion ?>
		- <a href="<?php echo $view->generateUrl('usersLogout') ?>"><?php
		_e('c_c_user_Log_off_action')?></a>
		<?php # fin Okatea : lien déconnexion ?>
	</p>

	<?php # début Okatea : date de dernière visite ?>
	<p><?php printf(__('c_c_user_last_visit_on_%s'), DateTime::full($okt->user->last_visit)) ?></p>
	<?php # fin Okatea : date de dernière visite ?>

<?php else : ?>

	<p>
		<?php _e('c_c_user_hello_you_are_not_logged')?>

		<?php # début Okatea : lien page connexion
		if ($okt['config']->users['pages']['login']) : ?>
		- <a
			href="<?php echo $okt['router']->generateLoginUrl($okt->request->getUri()) ?>"><?php
		_e('c_c_auth_login')?></a>
		<?php endif; # fin Okatea : lien page connexion ?>

		<?php # début Okatea : lien page inscription
		if ($okt['config']->users['pages']['register']) : ?>
		- <a href="<?php echo $okt['router']->generateregisterUrl() ?>"><?php
		_e('c_c_auth_register')?></a>
		<?php endif; # fin Okatea : lien page inscription ?>

		<?php # début Okatea : lien page mot de passe oublié
		if ($okt['config']->users['pages']['forget_password']) : ?>
		- <a href="<?php echo $view->generateUrl('usersForgetPassword') ?>"><?php
		_e('c_c_auth_forgot_password')?></a>
		<?php endif; # fin Okatea : lien page mot de passe oublié ?>
	</p>

<?php endif; ?>
</div><!-- #userbar -->
