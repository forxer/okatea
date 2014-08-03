
<?php 
# début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(__DIR__ . '/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<div id="userbar">
<?php if (!$okt['visitor']->infos->is_guest) : ?>

	<p>
		<?php printf(__('c_c_user_hello_%s'), $view->escape($okt['visitor']->usedname))?>

		<?php 
# début Okatea : lien page profil
	if ($okt->users->config->enable_profile_page)
	:
		?>
		- <a href="<?php echo $view->generateUrl('usersProfile') ?>"><?php _e('c_c_user_profile') ?></a>
		<?php endif; # fin Okatea : lien page profil ?>

		<?php # début Okatea : lien déconnexion ?>
		- <a href="<?php echo $view->generateUrl('usersLogout') ?>"><?php
	_e('c_c_user_Log_off_action')?></a>
		<?php # fin Okatea : lien déconnexion ?>
	</p>

	<?php # début Okatea : date de dernière visite ?>
	<p><?php printf(__('c_c_user_last_visit_on_%s'), dt::str(__('%A, %B %d, %Y, %H:%M'), $okt['visitor']->last_visit)); ?></p>
	<?php # fin Okatea : date de dernière visite ?>

<?php else : ?>

	<p>
		<?php _e('c_c_user_hello_you_are_not_logged')?>

		<?php 
# début Okatea : lien page connexion
	if ($okt->users->config->enable_login_page)
	:
		?>
		- <a
			href="<?php echo $view->escape(UsersHelpers::getLoginUrl($okt['request']->getUri())) ?>"><?php
		_e('c_c_auth_login')?></a>
		<?php endif; # fin Okatea : lien page connexion ?>

		<?php 
# début Okatea : lien page inscription
	if ($okt->users->config->enable_register_page)
	:
		?>
		- <a
			href="<?php echo $view->escape(UsersHelpers::getRegisterUrl()) ?>"><?php
		_e('c_c_auth_register')?></a>
		<?php endif; # fin Okatea : lien page inscription ?>

		<?php 
# début Okatea : lien page mot de passe oublié
	if ($okt->users->config->enable_forget_password_page)
	:
		?>
		- <a href="<?php echo $view->generateUrl('usersForgetPassword') ?>"><?php
		_e('c_c_auth_forgot_password')?></a>
		<?php endif; # fin Okatea : lien page mot de passe oublié ?>
	</p>

<?php endif; ?>
</div>
<!-- #userbar -->
