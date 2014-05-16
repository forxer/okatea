<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
?>
<?php printf(__('c_c_emails_hello_%s'), $view->escape($user))?>

<?php printf(__('c_c_emails_request_new_password_on_%s'), $view->escape($site_title), $view->escape($site_url))?>

<?php _e('c_c_emails_request_new_password_not_requested')?>
<?php _e('c_c_emails_request_new_password_updated_after_validate')?>

<?php printf(__('c_c_emails_request_new_password_is_%s'), $view->escape($password))?>

<?php printf(__('c_c_emails_request_new_password_validate_on_%s'), $view->escape($validate_url))?>


<?php _e('c_c_emails_best_regards')?>

--
<?php _e('c_c_emails_automatic_email')?>
<?php _e('c_c_emails_do_not_reply')?>
