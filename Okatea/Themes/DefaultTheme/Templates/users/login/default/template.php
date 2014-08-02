<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

# title tag
$okt->page->addTitleTag(__('c_c_auth_login'));

# titre de la page
$okt->page->setTitle(__('c_c_auth_login'));

# titre SEO de la page
$okt->page->setTitleSeo(__('c_c_auth_login'));

$okt->page->meta_description = $okt->page->getSiteMetaDesc();

$okt->page->meta_keywords = $okt->page->getSiteMetaKeywords();

# fil d'ariane
$okt->page->breadcrumb->add(__('c_c_auth_login'), $this->okt['router']->generateLoginUrl());

$view->extend('Layout');

?>

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
$okt->page->js->addFile($okt->options->public_url . '/components/jquery/dist/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php 
# début Okatea : validation JS du formulaire
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


<?php 
# début Okatea : affichage des éventuelles erreurs
if ($okt->error->notEmpty())
:
	?>
<div class="errors_box">
		<?php echo $okt->error->get(); ?>
	</div>
<?php endif; # fin Okatea : affichage des éventuelles erreurs ?>


<form id="login-form" class="userform"
	action="<?php echo $view->generateUrl('usersLogin') ?>" method="post">

	<p class="field">
		<label for="user_id"><?php if ($okt->config->users['registration']['merge_username_email']) { _e('c_c_Email'); } else { _e('c_c_user_Username'); } ?></label>
		<input name="user_id" id="user_id" type="text" maxlength="225"
			value="<?php echo $view->escape($user_id) ?>" />
	</p>

	<p class="field">
		<label for="user_pwd"><?php _e('c_c_user_Password') ?></label> <input
			name="user_pwd" id="user_pwd" type="password" maxlength="225" />
	</p>

	<p>
		<input type="checkbox" id="user_remember" name="user_remember"
			value="1" /> <label for="user_remember"><?php _e('c_c_auth_remember_me') ?></label>
	</p>

	<p>
		<input type="hidden" name="redirect"
			value="<?php echo rawurlencode($redirect) ?>" /> <input type="hidden"
			name="sended" value="1" /> <input class="submit btn" type="submit"
			value="<?php _e('c_c_auth_login_action') ?>" />
	</p>

	<p class="note"><?php _e('c_c_auth_must_accept_cookies_private_area') ?></p>

	<ul>
		<?php 
# début Okatea : lien page mot de passe oublié
		if ($okt->config->users['pages']['forget_password'])
		:
			?>
		<li><a href="<?php echo $view->generateUrl('usersForgetPassword') ?>"><?php
			_e('c_c_auth_forgot_password')?></a></li>
		<?php endif; # fin Okatea : lien page mot de passe oublié ?>

		<?php 
# début Okatea : lien page inscription
		if ($okt->config->users['pages']['register'])
		:
			?>
		<li><a href="<?php echo $view->generateUrl('usersRegister') ?>"><?php
			_e('c_c_auth_register')?></a></li>
		<?php endif; # fin Okatea : lien page inscription ?>
	</ul>

</form>
