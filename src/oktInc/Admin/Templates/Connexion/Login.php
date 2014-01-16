<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$view->extend('layout');

$okt->page->addGlobalTitle(__('c_c_auth_login'));

$okt->page->js->addReady('
	$("#user_id").focus();
');

?>

<form action="<?php echo $view->generateUrl('login') ?>" method="post">

	<p class="field"><label for="user_id" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_user_Username') ?></label>
	<input type="text" id="user_id" name="user_id" size="30" maxlength="255" value="<?php $view->escapeHtmlAttr($sUserId) ?>" /></p>

	<p class="field"><label for="user_pwd" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_user_Password') ?></label>
	<input type="password" id="user_pwd" name="user_pwd" size="30" maxlength="255" value="" /></p>

	<p><input type="checkbox" id="user_remember" name="user_remember" value="1" />
	<label class="inline" for="user_remember"><?php _e('c_c_auth_remember_me') ?></label></p>

	<p><?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_auth_login_action') ?>" /></p>

	<p class="note"><?php _e('c_c_auth_must_accept_cookies_private_area') ?></p>

	<p><a href="<?php echo $view->generateUrl('forget_password') ?>"><?php _e('c_c_auth_forgot_password') ?></a></p>
</form>