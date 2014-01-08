<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tao\Admin\Page;
use Tao\Admin\Menu as AdminMenu;
use Tao\Core\LogAdmin;
use Tao\Misc\Utilities as util;

/**
 * Fichier commun au frontoffice
 *
 */

# On inclu le fichier prepend général
require_once __DIR__.'/../prepend.php';

# Initialisation des pages de l'administration
$okt->page = new Page($okt);

# Initialisation des journaux admin
$okt->logAdmin = new LogAdmin($okt);

# Vérification de l'utilisateur en cours
if (!defined('OKT_SKIP_USER_ADMIN_CHECK'))
{
	# on stocke l'URL de la page dans un cookie
	$okt->user->setAuthFromCookie($okt->request->getUri());

	# si c'est un invité, rien à faire ici
	if ($okt->user->is_guest)
	{
		$okt->page->flash->warning(__('c_c_auth_not_logged_in'));
		http::redirect(OKT_ADMIN_LOGIN_PAGE);
	}

	# si il n'a pas la permission, il dégage
	elseif (!$okt->checkPerm('usage'))
	{
		$okt->user->logout();
		$okt->page->flash->error(__('c_c_auth_restricted_access'));
		http::redirect(OKT_ADMIN_LOGIN_PAGE);
	}

	# enfin, si on est en maintenance, il faut être superadmin
	elseif ($okt->config->admin_maintenance_mode && !$okt->user->is_superadmin)
	{
		$okt->user->logout();
		$okt->page->flash->error(__('c_c_auth_admin_maintenance_mode'));
		http::redirect(OKT_ADMIN_LOGIN_PAGE);
	}
}

# Demande de déconnexion
if (!empty($_REQUEST['logout']))
{
	$okt->user->setAuthFromCookie('');
	$okt->user->logout();
	http::redirect(OKT_ADMIN_LOGIN_PAGE);
}

# Validation du CSRF token
if (!defined('OKT_SKIP_CSRF_CONFIRM') && !empty($_POST) && (!isset($_POST['csrf_token']) || $okt->user->csrf_token !== $_POST['csrf_token']))
{
	$okt->user->logout();
	$okt->page->flash->error(__('c_c_auth_bad_csrf_token'));
	http::redirect(OKT_ADMIN_LOGIN_PAGE);
}

# Permissions de base de l'administration
$okt->addPerm('usage', __('c_a_def_perm_usage'));

$okt->addPermGroup('configuration', __('c_a_def_perm_config'));
	$okt->addPerm('configsite', 	__('c_a_def_perm_config_website'), 'configuration');
	$okt->addPerm('display', 		__('c_a_def_perm_config_display'), 'configuration');
	$okt->addPerm('languages', 		__('c_a_def_perm_config_local'), 'configuration');
	$okt->addPerm('modules', 		__('c_a_def_perm_config_modules'), 'configuration');
	$okt->addPerm('themes', 		__('c_a_def_perm_config_themes'), 'configuration');
	$okt->addPerm('navigation', 	__('c_a_def_perm_config_navigation'), 'configuration');
	$okt->addPerm('permissions', 	__('c_a_def_perm_config_perms'), 'configuration');
	$okt->addPerm('tools', 			__('c_a_def_perm_config_tools'), 'configuration');
	$okt->addPerm('infos', 			__('c_a_def_perm_config_infos'), 'configuration');

# Title tag
$okt->page->addTitleTag($okt->page->getSiteTitleTag(null,$okt->page->getSiteTitle()));

# Fil d'ariane administration
$okt->page->addAriane(__('Administration'),'index.php');

# Initialisation menu principal et ses sous-menus
if (!defined('OKT_DISABLE_MENU'))
{
	# Menu principal
	$okt->page->mainMenu = new AdminMenu(
		'mainMenu-'.($okt->config->admin_sidebar_position == 0 ? 'left' : 'right'),
		Page::$formatHtmlMainMenu);

	# Accueil
	$okt->page->mainMenu->add(
		/* titre*/ 		__('c_a_menu_home'),
		/* URL */ 		'index.php',
		/* actif ? */	(OKT_FILENAME == 'index.php'),
		/* position */	1,
		/* visible ? */	true,
		/* ID */ 		null,
		/* Sub */		($okt->page->homeSubMenu = new AdminMenu(null,Page::$formatHtmlSubMenu)),
		/* Icon */		$okt->options->public_url.'/img/admin/start-here.png'
	);
		$okt->page->homeSubMenu->add(
			__('c_a_menu_roundabout'),
			'index.php',
			(OKT_FILENAME == 'index.php'),
			10,
			true
		);

	# Configuration
	$okt->page->mainMenu->add(
		__('c_a_menu_configuration'),
		'configuration.php',
		(OKT_FILENAME == 'configuration.php'),
		10000000,
		$okt->checkPerm('configsite'),
		null,
		($okt->page->configSubMenu = new AdminMenu(null,Page::$formatHtmlSubMenu)),
		$okt->options->public_url.'/img/admin/network-server.png'
	);
		$okt->page->configSubMenu->add(__('c_a_menu_general'), 'configuration.php?action=site',
			(OKT_FILENAME == 'configuration.php') && (!$okt->page->action || $okt->page->action === 'site'),
			10,
			$okt->checkPerm('configsite')
		);
		$okt->page->configSubMenu->add(__('c_a_menu_display'), 'configuration.php?action=display',
			(OKT_FILENAME == 'configuration.php') && ($okt->page->action === 'display'),
			20,
			$okt->checkPerm('display')
		);
		$okt->page->configSubMenu->add(__('c_a_menu_localization'), 'configuration.php?action=languages',
			(OKT_FILENAME == 'configuration.php') && ($okt->page->action === 'languages'),
			60,
			$okt->checkPerm('languages')
		);
		$okt->page->configSubMenu->add(__('c_a_menu_modules'), 'configuration.php?action=modules',
			(OKT_FILENAME == 'configuration.php') && ($okt->page->action === 'modules'),
			70,
			$okt->checkPerm('modules')
		);
		$okt->page->configSubMenu->add(__('c_a_menu_themes'), 'configuration.php?action=themes',
			(OKT_FILENAME == 'configuration.php') && ($okt->page->action === 'themes' || $okt->page->action === 'theme'),
			80,
			$okt->checkPerm('themes')
		);
		$okt->page->configSubMenu->add(__('c_a_menu_navigation'), 'configuration.php?action=navigation',
			(OKT_FILENAME == 'configuration.php') && ($okt->page->action === 'navigation' || $okt->page->action === 'navigation'),
			90,
			$okt->checkPerm('navigation')
		);
		$okt->page->configSubMenu->add(__('c_a_menu_permissions'), 'configuration.php?action=permissions',
			(OKT_FILENAME == 'configuration.php') && ($okt->page->action === 'permissions'),
			100,
			$okt->checkPerm('permissions')
		);
		$okt->page->configSubMenu->add(__('c_a_menu_tools'), 'configuration.php?action=tools',
			(OKT_FILENAME == 'configuration.php') && ($okt->page->action === 'tools'),
			110,
			$okt->checkPerm('tools')
		);
		$okt->page->configSubMenu->add(__('c_a_menu_infos'), 'configuration.php?action=infos',
			(OKT_FILENAME == 'configuration.php') && ($okt->page->action === 'infos'),
			120,
			$okt->checkPerm('infos')
		);
		$okt->page->configSubMenu->add(__('c_a_menu_update'), 'configuration.php?action=update',
			(OKT_FILENAME == 'configuration.php') && ($okt->page->action === 'update'),
			130,
			$okt->config->update_enabled && $okt->checkPerm('is_superadmin')
		);
		$okt->page->configSubMenu->add(__('c_a_menu_log_admin'), 'configuration.php?action=logadmin',
			(OKT_FILENAME == 'configuration.php') && ($okt->page->action === 'logadmin'),
			140,
			$okt->checkPerm('is_superadmin')
		);
		$okt->page->configSubMenu->add(__('c_a_menu_router'), 'configuration.php?action=router',
			(OKT_FILENAME == 'configuration.php') && ($okt->page->action === 'router'),
			150,
			$okt->checkPerm('is_superadmin')
		);
		$okt->page->configSubMenu->add(__('c_a_menu_advanced'), 'configuration.php?action=advanced',
			(OKT_FILENAME == 'configuration.php') && ($okt->page->action === 'advanced'),
			160,
			$okt->checkPerm('is_superadmin')
		);
}

# Affichage avertissement si le mode maintenance est activé
if ($okt->config->public_maintenance_mode) {
	$okt->page->warnings->set(__('c_a_public_maintenance_mode_enabled'));
}
if ($okt->config->admin_maintenance_mode) {
	$okt->page->warnings->set(__('c_a_admin_maintenance_mode_enabled'));
}

# Ajout des fichiers CSS de l'admin
$okt->page->css->addFile($okt->options->public_url.'/ui-themes/'.$okt->config->admin_theme.'/jquery-ui.css');
$okt->page->css->addFile($okt->options->public_url.'/css/init.css');
$okt->page->css->addFile($okt->options->public_url.'/css/admin.css');
$okt->page->css->addFile($okt->options->public_url.'/css/famfamfam.css');

# Ajout des fichiers JS de l'admin
$okt->page->js->addFile($okt->options->public_url.'/components/jquery/jquery.min.js');
$okt->page->js->addFile($okt->options->public_url.'/components/jquery-cookie/jquery.cookie.js');
$okt->page->js->addFile($okt->options->public_url.'/js/jquery/metadata/jquery.metadata.min.js');
$okt->page->js->addFile($okt->options->public_url.'/js/jquery/ui/jquery-ui.min.js');
$okt->page->js->addFile($okt->options->public_url.'/js/jquery/validate/jquery.validate.min.js');
$okt->page->js->addFile($okt->options->public_url.'/js/jquery/validate/additional-methods.min.js');
$okt->page->js->addFile($okt->options->public_url.'/js/common_admin.js');

# Chargement des parties admin des modules
$okt->modules->loadModules('admin',$okt->user->language);

# Chargement des éventuelles traductions personalisées
$okt->l10n->loadFile(OKT_THEME_PATH.'/locales/'.$okt->user->language.'/custom');
