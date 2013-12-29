
<?php $this->extend('layout'); ?>


<?php $okt->page->js->addReady('
	$("#user_id").focus();
'); ?>


<form action="<?php echo $view->escapeHtmlAttr($view->generate('login')) ?>" method="post">

	<p class="field"><label for="user_id" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_user_Username') ?></label>
	<?php echo form::text('user_id', 30, 255, $sUserId) ?></p>

	<p class="field"><label for="user_pwd" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_user_Password') ?></label>
	<?php echo form::password('user_pwd', 30, 255, '') ?></p>

	<p><?php echo form::checkbox('user_remember', 1) ?>
	<label class="inline" for="user_remember"><?php _e('c_c_auth_remember_me') ?></label></p>

	<p><?php //echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_auth_login_action') ?>" /></p>

	<p class="note"><?php _e('c_c_auth_must_accept_cookies_private_area') ?></p>

	<p><a href="<?php echo '' ?>?action=forget"><?php _e('c_c_auth_forgot_password') ?></a></p>
</form>