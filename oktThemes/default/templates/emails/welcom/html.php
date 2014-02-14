<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$view->extend('emails/layout');

?>
<p><?php printf(__('c_c_emails_hello_%s'), $view->escape($user)) ?></p>

<p><?php printf(__('c_c_emails_thanks_register_on_%s'), $view->escape($site_title), $view->escape($site_title)) ?></p>

<p><?php _e('c_c_emails_details_of_account_are') ?></p>

<ul>
	<li><?php printf(__('c_c_emails_Username_%s'), $view->escape($username)) ?></li>
	<li><?php printf(__('c_c_emails_Password_%s'), $view->escape($password)) ?></li>
</ul>

<?php if ($okt->config->users['registration']['validation_email']) : ?>
<p><?php printf(__('c_c_emails_validate_account_%s'), $view->escape($validate_url)) ?></p>
<?php endif; ?>

<?php if ($okt->config->users['registration']['validation_admin']) : ?>
<p><?php _e('c_c_emails_admin_will_validate_account') ?></p>
<?php endif; ?>

<p><?php _e('c_c_emails_best_regards') ?></p>

<p><em><?php _e('c_c_emails_automatic_email') ?></em><br>
<em><?php _e('c_c_emails_do_not_reply') ?></em></p>
