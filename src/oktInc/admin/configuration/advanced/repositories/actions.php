<?php
/**
 * Configuration avancée dépôts (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


if (!empty($_POST['form_sent']))
{
	$p_modules_repositories_enabled = !empty($_POST['p_modules_repositories_enabled']) ? true : false;

	$p_modules_repositories = array();
	$p_modules_repositories_names = !empty($_POST['p_modules_repositories_names']) && is_array($_POST['p_modules_repositories_names']) ? $_POST['p_modules_repositories_names'] : array();
	$p_modules_repositories_urls = !empty($_POST['p_modules_repositories_urls']) && is_array($_POST['p_modules_repositories_urls']) ? $_POST['p_modules_repositories_urls'] : array();

	foreach ($p_modules_repositories_names as $i=>$name)
	{
		if (!empty($p_modules_repositories_urls[$i])) {
			$p_modules_repositories[$name] = $p_modules_repositories_urls[$i];
		}
	}

	$p_themes_repositories_enabled = !empty($_POST['p_themes_repositories_enabled']) ? true : false;

	$p_themes_repositories = array();
	$p_themes_repositories_names = !empty($_POST['p_themes_repositories_names']) && is_array($_POST['p_themes_repositories_names']) ? $_POST['p_themes_repositories_names'] : array();
	$p_themes_repositories_urls = !empty($_POST['p_themes_repositories_urls']) && is_array($_POST['p_themes_repositories_urls']) ? $_POST['p_themes_repositories_urls'] : array();

	foreach ($p_themes_repositories_names as $i=>$name)
	{
		if (!empty($p_themes_repositories_urls[$i])) {
			$p_themes_repositories[$name] = $p_themes_repositories_urls[$i];
		}
	}

	$aPageData['aNewConf'] = array_merge($aPageData['aNewConf'], array(
		'modules_repositories_enabled' => (boolean)$p_modules_repositories_enabled,
		'modules_repositories' => (array)$p_modules_repositories,

		'themes_repositories_enabled' => (boolean)$p_themes_repositories_enabled,
		'themes_repositories' => (array)$p_themes_repositories
	));
}
