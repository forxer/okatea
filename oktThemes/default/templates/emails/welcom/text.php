<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

?>
<?php printf(__('c_c_emails_hello_%s'), $view->escape($user)) ?>

<?php printf(__('c_c_emails_thanks_register_on_%s'), $view->escape($site_title), $view->escape($site_title)) ?>

<?php _e('c_c_emails_details_of_account_are') ?>

<?php printf(__('c_c_emails_Username_%s'), $view->escape($username)) ?>
<?php printf(__('c_c_emails_Password_%s'), $view->escape($password)) ?>

<?php if ($okt->config->users['registration']['validation_email']) : ?>
<?php printf(__('c_c_emails_validate_account_%s'), $view->escape($validate_url)) ?>
<?php endif; ?>

<?php if ($okt->config->users['registration']['validation_admin']) : ?>
<?php _e('c_c_emails_admin_will_validate_account') ?>
<?php endif; ?>

<?php _e('c_c_emails_best_regards') ?>

--
<?php _e('c_c_emails_automatic_email') ?>
<?php _e('c_c_emails_do_not_reply') ?>
