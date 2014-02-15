<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

?>
<?php printf(__('c_c_emails_hello_%s'), $view->escape($user)) ?>


<?php printf(__('c_c_emails_admin_update_password_on_%s'), $view->escape($site_title), $view->escape($site_url)) ?>

<?php printf(__('c_c_emails_new_update_password_is_%s'), $view->escape($password)) ?>


<?php _e('c_c_emails_best_regards') ?>

--
<?php _e('c_c_emails_automatic_email') ?>
<?php _e('c_c_emails_do_not_reply') ?>
