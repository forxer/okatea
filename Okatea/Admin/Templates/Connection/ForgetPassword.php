<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$view->extend('Layout');

$okt->page->addGlobalTitle(__('c_c_auth_request_password'));

?>

<?php if ($bPasswordUpdated) : ?>

<p><?php _e('c_c_auth_password_updated') ?></p>
<p>
	<a href="<?php echo $view->generateUrl('login') ?>"><?php _e('c_c_auth_login') ?></a>
</p>

<?php elseif ($bPasswordSended) : ?>

<p><?php _e('c_c_auth_email_sent_with_instructions') ?></p>
<p>
	<a href="<?php echo $view->generateUrl('login') ?>"><?php _e('c_c_auth_login') ?></a>
</p>

<?php else : ?>

<form action="<?php echo $view->generateUrl('forget_password') ?>"
	method="post">

	<p class="field">
		<label for="email"><?php _e('c_c_auth_give_account_email') ?></label>
		<input type="text" id="email" name="email" size="30" maxlength="255" />
	</p>
	<p class="note"><?php _e('c_c_auth_new_password_link_activate_will_be_sent') ?></p>

	<p><?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_Send') ?>" /> <a
			href="<?php echo $view->generateUrl('login') ?>"><?php _e('c_c_action_Go_back') ?></a>
	</p>
</form>

<?php endif; ?>