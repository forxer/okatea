<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

?>

<h3><?php _e('c_a_users_config_tab_tpl_title') ?></h3>

<h4><?php _e('c_a_users_config_tpl_forgotten_password') ?></h4>
<?php echo $oTemplatesForgottenPassword->getHtmlConfigUsablesTemplates(false)?>

<h4><?php _e('c_a_users_config_tpl_login') ?></h4>
<?php echo $oTemplatesLogin->getHtmlConfigUsablesTemplates(false)?>

<h4><?php _e('c_a_users_config_tpl_login_register') ?></h4>
<?php echo $oTemplatesLoginRegister->getHtmlConfigUsablesTemplates(false)?>

<h4><?php _e('c_a_users_config_tpl_profile') ?></h4>
<?php echo $oTemplatesProfile->getHtmlConfigUsablesTemplates(false)?>

<h4><?php _e('c_a_users_config_tpl_register') ?></h4>
<?php echo $oTemplatesRegister->getHtmlConfigUsablesTemplates(false)?>

<h4><?php _e('c_a_users_config_tpl_user_bar') ?></h4>
<?php echo $oTemplatesUserBar->getHtmlConfigUsablesTemplates(false)?>
