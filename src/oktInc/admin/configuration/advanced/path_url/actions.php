<?php
/**
 * Configuration avancée chemins et URL (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


if (!empty($_POST['form_sent']))
{
	$p_app_path = !empty($_POST['p_app_path']) ? $_POST['p_app_path'] : '/';
	$p_app_path = util::formatAppPath($p_app_path);

	$p_domain = !empty($_POST['p_domain']) ? $_POST['p_domain'] : '';
	$p_domain = util::formatAppPath($p_domain, false, false);

	$p_internal_router = !empty($_POST['p_internal_router']) ? true : false;

	$aPageData['aNewConf'] = array_merge($aPageData['aNewConf'], array(
		'app_path' => $p_app_path,
		'domain' => $p_domain,
		'internal_router' => $p_internal_router
	));
}
