<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * La page principale de la partie configuration
 *
 */

require __DIR__.'/../oktInc/admin/prepend.php';

define('ON_OKT_CONFIGURATION', true);


# Titre de la page
$okt->page->addGlobalTitle(__('Configuration'), 'configuration.php');


# inclusion du fichier requis
if ((!$okt->page->action || $okt->page->action == 'site') && $okt->checkPerm('configsite')) {
	require $okt->options->inc_dir.'/admin/configuration/site.php';
}
elseif ($okt->page->action == 'display' && $okt->checkPerm('display')) {
	require $okt->options->inc_dir.'/admin/configuration/display.php';
}
elseif ($okt->page->action == 'languages' && $okt->checkPerm('languages')) {
	require $okt->options->inc_dir.'/admin/configuration/languages.php';
}
elseif ($okt->page->action == 'modules' && $okt->checkPerm('modules')) {
	require $okt->options->inc_dir.'/admin/configuration/modules.php';
}
elseif ($okt->page->action == 'theme_editor' && $okt->checkPerm('themes')) {
	require $okt->options->inc_dir.'/admin/configuration/theme_editor.php';
}
elseif ($okt->page->action == 'themes' && $okt->checkPerm('themes')) {
	require $okt->options->inc_dir.'/admin/configuration/themes.php';
}
elseif ($okt->page->action == 'theme' && $okt->checkPerm('themes')) {
	require $okt->options->inc_dir.'/admin/configuration/theme.php';
}
elseif ($okt->page->action == 'permissions' && $okt->checkPerm('permissions')) {
	require $okt->options->inc_dir.'/admin/configuration/permissions.php';
}
elseif ($okt->page->action == 'navigation' && $okt->checkPerm('navigation')) {
	require $okt->options->inc_dir.'/admin/configuration/navigation.php';
}
elseif ($okt->page->action == 'tools' && $okt->checkPerm('tools')) {
	require $okt->options->inc_dir.'/admin/configuration/tools.php';
}
elseif ($okt->page->action == 'infos' && $okt->checkPerm('infos')) {
	require $okt->options->inc_dir.'/admin/configuration/infos.php';
}
elseif ($okt->page->action == 'update' && $okt->config->update_enabled && $okt->checkPerm('is_superadmin')) {
	require $okt->options->inc_dir.'/admin/configuration/update.php';
}
elseif ($okt->page->action == 'logadmin' && $okt->checkPerm('is_superadmin')) {
	require $okt->options->inc_dir.'/admin/configuration/logadmin.php';
}
elseif ($okt->page->action == 'router' && $okt->checkPerm('is_superadmin')) {
	require $okt->options->inc_dir.'/admin/configuration/router.php';
}
elseif ($okt->page->action == 'advanced' && $okt->checkPerm('is_superadmin')) {
	require $okt->options->inc_dir.'/admin/configuration/advanced.php';
}
else {
	http::redirect('index.php');
}
