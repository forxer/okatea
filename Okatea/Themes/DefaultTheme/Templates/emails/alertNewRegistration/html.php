<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//$view->extend('emails/layout');
?>
<p><?php printf(__('c_c_emails_hello_%s'), $view->escape($admin)) ?></p>

<p><?php printf(__('c_c_emails_new_user_%s_registered_on_%s'), $view->escape($user), $view->escape($site_title), $view->escape($site_url)) ?></p>

<?php if ($okt->config->users['registration']['validation_admin']) : ?>
<p><?php printf(__('c_c_emails_validate_user_on_%s'), $user_edit_url) ?></p>
<?php endif; ?>


<p><?php _e('c_c_emails_best_regards') ?></p>

<p>
	<em><?php _e('c_c_emails_automatic_email') ?></em><br> <em><?php _e('c_c_emails_do_not_reply') ?></em>
</p>
