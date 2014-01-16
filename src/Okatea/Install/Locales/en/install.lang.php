<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

# common
$GLOBALS['__l10n']['i_install_interface'] = 'Installation interface';
$GLOBALS['__l10n']['i_update_interface'] = 'Update interface';
$GLOBALS['__l10n']['i_errors'] = 'Error(s)';

# steps
$GLOBALS['__l10n']['i_step_start'] = 'home';
$GLOBALS['__l10n']['i_step_checks'] = 'pre-requisites';
$GLOBALS['__l10n']['i_step_db_conf'] = 'database';
$GLOBALS['__l10n']['i_step_db'] = 'tables';
$GLOBALS['__l10n']['i_step_supa'] = 'users';
$GLOBALS['__l10n']['i_step_config'] = 'configuration';
$GLOBALS['__l10n']['i_step_log'] = 'registration';
$GLOBALS['__l10n']['i_step_theme'] = 'theme';
$GLOBALS['__l10n']['i_step_colors'] = 'colors';
$GLOBALS['__l10n']['i_step_modules'] = 'modules';
$GLOBALS['__l10n']['i_step_pages'] = 'pages';
$GLOBALS['__l10n']['i_step_merge_config'] = 'merge configuration';
$GLOBALS['__l10n']['i_step_end'] = 'end';

# start
$GLOBALS['__l10n']['i_start_about_install'] = 'You are about to <strong>install</strong> Okatea %s.';
$GLOBALS['__l10n']['i_start_about_update'] = 'You are about to <strong>update</strong> Okatea to <em>%s</em> version.';

$GLOBALS['__l10n']['i_start_choose_lang'] = 'You can choose the language of the interface:';
$GLOBALS['__l10n']['i_start_click_next'] = 'To continue please click the "Next" button below.';

# checks
$GLOBALS['__l10n']['i_checks_title'] = 'Checking pre-requisites';
$GLOBALS['__l10n']['i_checks_warning'] = '<strong>Warning:</strong> the system audit issued alerts did not prevent the system from working but it is possible that some features are failing.';
$GLOBALS['__l10n']['i_checks_big_loose'] = 'The configuration server has major problems. The system can not be installed on this server.';

# db conf
$GLOBALS['__l10n']['i_db_conf_title'] = 'Connecting to the database';
$GLOBALS['__l10n']['i_db_conf_ok'] = 'Connection to database successful, connection file created. Click Next to create the tables.';
$GLOBALS['__l10n']['i_db_conf_environement_choice'] = 'Test the connection on the environment:';
$GLOBALS['__l10n']['i_db_conf_environement_prod'] = 'production';
$GLOBALS['__l10n']['i_db_conf_environement_dev'] = 'development';
$GLOBALS['__l10n']['i_db_conf_environement_note'] = 'You must choose the environment in which you are installing the system.';
$GLOBALS['__l10n']['i_db_conf_prod_server'] = 'Production server';
$GLOBALS['__l10n']['i_db_conf_dev_server'] = 'Development server';
$GLOBALS['__l10n']['i_db_conf_db_host'] = 'Database host';
$GLOBALS['__l10n']['i_db_conf_db_name'] = 'Database name';
$GLOBALS['__l10n']['i_db_conf_db_username'] = 'Database username';
$GLOBALS['__l10n']['i_db_conf_db_password'] = 'Database password';
$GLOBALS['__l10n']['i_db_conf_db_prefix'] = 'Tables prefix';
$GLOBALS['__l10n']['i_db_conf_db_error_prod_prefix_form'] = 'The prefix for the development environment is invalid. It can only contain letters and "_" character.';
$GLOBALS['__l10n']['i_db_conf_db_error_dev_prefix_form'] = 'The prefix for the production environment is invalid. It can only contain letters and "_" character.';
$GLOBALS['__l10n']['i_db_conf_db_error_prod_must_prefix'] = 'You must enter a prefix for the database production environment.';
$GLOBALS['__l10n']['i_db_conf_db_error_dev_must_prefix'] = 'You must enter a prefix for the database development environment.';
$GLOBALS['__l10n']['i_db_conf_db_error_prod_must_host'] = 'You must enter a host database for the production environment.';
$GLOBALS['__l10n']['i_db_conf_db_error_dev_must_host'] = 'You must enter a host database for the development environment.';
$GLOBALS['__l10n']['i_db_conf_db_error_prod_must_name'] = 'You must enter a database for the production environment.';
$GLOBALS['__l10n']['i_db_conf_db_error_dev_must_name'] = 'You must enter a database for the development environment.';
$GLOBALS['__l10n']['i_db_conf_db_error_prod_must_username'] = 'You must enter a username database for the production environment.';
$GLOBALS['__l10n']['i_db_conf_db_error_dev_must_username'] = 'You must enter a username database for the development environment.';

# connexion
$GLOBALS['__l10n']['i_connexion_title'] = 'Connecting to the database';
$GLOBALS['__l10n']['i_connexion_success'] = 'Connecting to the database successfully. Click next to update the tables.';

# db
$GLOBALS['__l10n']['i_db_title'] = 'Creating tables';
$GLOBALS['__l10n']['i_db_warning'] = '<strong>Warning:</strong> the system audit issued alerts but this should not pose any problems.';
$GLOBALS['__l10n']['i_db_big_loose'] = 'Fatal errors occurred, unable to continue the installation.';

# supa
$GLOBALS['__l10n']['i_supa_title'] = 'Create administrator accounts';
$GLOBALS['__l10n']['i_supa_account_sudo'] = 'Super-administrator account';
$GLOBALS['__l10n']['i_supa_account_sudo_note'] = 'The super administrator account is the account that has full permissions. It’s you :)';
$GLOBALS['__l10n']['i_supa_account_admin'] = 'Administrator account';
$GLOBALS['__l10n']['i_supa_account_admin_note'] = 'The administrator account is an account that has permissions by default, but not all. It can provide access to the website administration but not all features. Useful for example to allow another person to manage the website or just having a clean interface for managing everyday. This account is optional, it can be created later if needed.';
$GLOBALS['__l10n']['i_supa_username'] = 'Username';
$GLOBALS['__l10n']['i_supa_password'] = 'Password';
$GLOBALS['__l10n']['i_supa_email'] = 'Email address';
$GLOBALS['__l10n']['i_supa_must_sudo_username'] = 'You must enter a username for the super-administrator account.';
$GLOBALS['__l10n']['i_supa_must_admin_username'] = 'You must enter a username for the administrator account.';
$GLOBALS['__l10n']['i_supa_must_sudo_password'] = 'You must enter a password for the super-administrator account.';
$GLOBALS['__l10n']['i_supa_must_admin_password'] = 'You must enter a password for the administrator account.';
$GLOBALS['__l10n']['i_supa_must_sudo_email'] = 'You must enter an email address for the super-administrator account.';
$GLOBALS['__l10n']['i_supa_must_admin_email'] = 'You must enter an email address for the administrator account.';

# configuration
$GLOBALS['__l10n']['i_config_title'] = 'Configuration de base';

# theme
$GLOBALS['__l10n']['i_theme_title'] = 'Theme choice';

# colors
$GLOBALS['__l10n']['i_colors_title'] = 'Theme colors';

# modules
$GLOBALS['__l10n']['i_modules_title'] = 'Installation of the first modules';

# pages
$GLOBALS['__l10n']['i_pages_title'] = 'Creation of the first pages';
$GLOBALS['__l10n']['i_pages_no_module_pages'] = 'The pages module is not installed, you can not create a page.';
$GLOBALS['__l10n']['i_pages_page_title_%s'] = 'Title of page %s';
$GLOBALS['__l10n']['i_pages_page_content_%s'] = 'Content of page %s';
$GLOBALS['__l10n']['i_pages_page_home_%s'] = 'Set page %s as homepage';
$GLOBALS['__l10n']['i_pages_page_no_home'] = 'No homepage yet';
$GLOBALS['__l10n']['i_pages_add_one_more'] = 'Add one more page';
$GLOBALS['__l10n']['i_pages_first_home_title'] = 'Home';
$GLOBALS['__l10n']['i_pages_first_home_content'] = "Welcom to our new website.\n\nThis website is currently under construction, thank you to return later reference.";
$GLOBALS['__l10n']['i_pages_first_about_title'] = 'About';
$GLOBALS['__l10n']['i_pages_first_default_content'] = 'This website is currently under construction, thank you to return later reference.';

# merge config
$GLOBALS['__l10n']['i_merge_config_title'] = 'Merging configuration data';
$GLOBALS['__l10n']['i_merge_config_done'] = 'The configuration data were merged successfully.';
$GLOBALS['__l10n']['i_merge_config_not'] = 'The configuration data have not been merged.';

# end
$GLOBALS['__l10n']['i_end_install_title'] = 'This is the end... of the installation';
$GLOBALS['__l10n']['i_end_update_title'] = 'This is the end... of the update';

$GLOBALS['__l10n']['i_end_install_congrat'] = 'Congratulations! You have successfully installed the system.';
$GLOBALS['__l10n']['i_end_update_congrat'] = 'Congratulations! You have successfully updated the system.';

$GLOBALS['__l10n']['i_end_connect'] = 'Log into <a href="%s">the administration interface</a> to configure the system.';
