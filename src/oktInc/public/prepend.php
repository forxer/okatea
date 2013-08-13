<?php
/**
 * Fichier commun au frontend
 *
 * @addtogroup Okatea
 *
 */


# On inclu le fichier prepend général
require_once dirname(__FILE__).'/../prepend.php';


# Librairies spécifiques à la partie publique
$oktAutoloadPaths['publicPage'] = dirname(__FILE__).'/lib.public.page.php';
$oktAutoloadPaths['publicPager'] = dirname(__FILE__).'/lib.public.pager.php';


# Initialisation des pages publiques
$okt->page = new publicPage($okt);
$okt->page->breadcrumb = new breadcrumb();


# Title tag
$okt->page->addTitleTag(util::getSiteTitleTag(null, util::getSiteTitle()));


# Chargement des parties public des modules
$okt->modules->loadModules('public', $okt->user->language);


# Chargement des éventuelles traductions personalisées
l10n::set(OKT_THEME_PATH.'/locales/'.$okt->user->language.'/custom');


# Initialisation du thème
if (file_exists(OKT_THEME_PATH.'/oktTheme.php'))
{
	require_once OKT_THEME_PATH.'/oktTheme.php';
	$okt->theme = new oktTheme($okt);
}


# Chargement des routes personnalisées
foreach ($okt->config->custom_routes as $iCustomRoute=>$aCustomRoute)
{
	$okt->router->addRoute('custom-route-'.$iCustomRoute, new oktRoute(
		$aCustomRoute['rep'], $aCustomRoute['class'], $aCustomRoute['method'],
		array($aCustomRoute['args'])
	));
}


# Chargement de la route par défaut
if (!empty($okt->config->default_route['class']) && !empty($okt->config->default_route['method']))
{
	$okt->router->addRoute('default-route', new oktRoute(
		'^/?$', $okt->config->default_route['class'], $okt->config->default_route['method'],
		array($okt->config->default_route['args'])
	));
}


# Start sessions
if (!session_id()) {
	session_start();
}


# Initialisation barre admin
if ($okt->user->is_superadmin || ($okt->user->is_admin && $okt->config->enable_admin_bar)) {
	$oPublicAdminBar = new oktPublicAdminBar($okt);
}
