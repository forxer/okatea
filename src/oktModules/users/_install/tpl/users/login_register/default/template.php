

<?php use Tao\Forms\Statics\FormElements as form; ?>

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


<?php # début Okatea : validation JS des formulaires
$okt->page->validateForm();
$okt->page->js->addReady("
	var log_validator = $('#login-form').validate({
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
	var reg_validator = $('#register-form').validate({
		rules: {
			add_username: {
				required: true,
				minlength: 2,
				maxlength: 255
			},
			add_email: {
				required: true,
				email: true
			},
			add_password: {
				required: true,
				minlength: 4
			},
			add_password_confirm: {
				required: true,
				equalTo: '#add_password'
			}
		}
	});
");
# fin Okatea : validation JS des formulaires ?>


<?php # début Okatea : affichage des éventuelles erreurs
if ($okt->error->notEmpty()) : ?>
	<div class="error_box">
		<?php echo $okt->error->get(); ?>
	</div>
<?php endif; # fin Okatea : affichage des éventuelles erreurs ?>



<div id="forms-log-reg" class="one-third-two-thirds">
	<div class="one-third">
		<form id="login-form" class="userform" action="<?php echo $view->escape($okt->page->getBaseUrl().$okt->users->config->public_log_reg_url[$okt->user->language]) ?>" method="post">
			<fieldset>
				<legend><?php _e('c_c_auth_login') ?></legend>

				<p class="field"><label for="user_id" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_user_Username') ?></label>
				<?php echo form::text('user_id', 30, 255, $view->escape($user_id)) ?></p>

				<p class="field"><label for="user_pwd" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_user_Password') ?></label>
				<?php echo form::password('user_pwd', 30, 255)?></p>

				<p><input type="checkbox" id="user_remember" name="user_remember" value="1" />
				<label for="user_remember"><?php _e('c_c_auth_remember_me') ?></label></p>

				<p><input type="hidden" name="redirect" value="<?php echo rawurlencode($redirect) ?>" />
				<input type="hidden" name="sended" value="1" />
				<input class="submit" type="submit" value="<?php _e('c_c_auth_login_action') ?>" /></p>
			</fieldset>

			<p class="note"><?php _e('c_c_auth_must_accept_cookies_private_area') ?></p>

			<?php # début Okatea : lien page mot de passe oublié
			if ($okt->users->config->enable_forget_password_page) : ?>
			<p><a href="<?php echo $view->escape(usersHelpers::getForgetPasswordUrl()) ?>"><?php
			_e('c_c_auth_forgot_password') ?></a></p>
			<?php endif; # fin Okatea : lien page mot de passe oublié ?>
		</form>
	</div><!-- .one-third -->
	<div class="two-thirds">
		<form id="register-form" class="userform" action="<?php echo $view->escape($okt->page->getBaseUrl().$okt->users->config->public_log_reg_url[$okt->user->language]) ?>" method="post">
			<fieldset>
				<legend><?php _e('c_c_auth_register') ?></legend>
				<div class="two-cols">
					<p class="field col"><label for="add_username" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_user_Username') ?></label>
					<?php echo form::text('add_username', 30, 255, $view->escape($aUserRegisterData['username'])) ?></p>

					<p class="field col"><label for="add_email" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_Email') ?></label>
					<?php echo form::text('add_email', 30, 255, $view->escape($aUserRegisterData['email'])) ?></p>

					<?php if ($okt->users->config->user_choose_group) : ?>
					<p class="field col"><label for="add_group_id"><?php _e('c_c_Group') ?></label>
					<?php echo form::select('add_group_id', $aUsersGroups, $view->escape($aUserRegisterData['group_id'])) ?></p>
					<?php endif; ?>
				</div>
				<div class="two-cols">
					<p class="field col"><label for="add_password" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_user_Password') ?></label>
					<?php echo form::password('add_password', 30, 255, $view->escape($aUserRegisterData['password'])) ?></p>

					<p class="field col"><label for="add_password_confirm" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_auth_confirm_password') ?></label>
					<?php echo form::password('add_password_confirm', 30, 255, $view->escape($aUserRegisterData['password_confirm'])) ?></p>
				</div>

				<p><?php echo form::hidden('redirect',$view->escape($redirect)) ?>
				<?php echo form::hidden('add_user',1) ?>
				<input type="submit" class="submit" value="<?php _e('c_c_auth_register_action') ?>" /></p>
			</fieldset>
		</form>

	</div><!-- .two-thirds -->
</div><!-- .one-third-two-thirds -->
