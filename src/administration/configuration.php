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
 * @addtogroup Okatea
 *
 */

require __DIR__.'/../oktInc/admin/prepend.php';

define('ON_CONFIGURATION_MODULE', true);


# Titre de la page
$okt->page->addGlobalTitle(__('Configuration'),'configuration.php');


# inclusion du fichier requis
if ((!$okt->page->action || $okt->page->action == 'site') && $okt->checkPerm('configsite')) {
	require OKT_INC_PATH.'/admin/configuration/site.php';
}
elseif ($okt->page->action == 'display' && $okt->checkPerm('display')) {
	require OKT_INC_PATH.'/admin/configuration/display.php';
}
elseif ($okt->page->action == 'languages' && $okt->checkPerm('languages')) {
	require OKT_INC_PATH.'/admin/configuration/languages.php';
}
elseif ($okt->page->action == 'modules' && $okt->checkPerm('modules')) {
	require OKT_INC_PATH.'/admin/configuration/modules.php';
}
elseif ($okt->page->action == 'theme_editor' && $okt->checkPerm('themes')) {
	require OKT_INC_PATH.'/admin/configuration/theme_editor.php';
}
elseif ($okt->page->action == 'themes' && $okt->checkPerm('themes')) {
	require OKT_INC_PATH.'/admin/configuration/themes.php';
}
elseif ($okt->page->action == 'theme' && $okt->checkPerm('themes')) {
	require OKT_INC_PATH.'/admin/configuration/theme.php';
}
elseif ($okt->page->action == 'permissions' && $okt->checkPerm('permissions')) {
	require OKT_INC_PATH.'/admin/configuration/permissions.php';
}
elseif ($okt->page->action == 'tools' && $okt->checkPerm('tools')) {
	require OKT_INC_PATH.'/admin/configuration/tools.php';
}
elseif ($okt->page->action == 'infos' && $okt->checkPerm('infos')) {
	require OKT_INC_PATH.'/admin/configuration/infos.php';
}
elseif ($okt->page->action == 'update' && $okt->config->update_enabled && $okt->checkPerm('is_superadmin')) {
	require OKT_INC_PATH.'/admin/configuration/update.php';
}
elseif ($okt->page->action == 'logadmin' && $okt->checkPerm('is_superadmin')) {
	require OKT_INC_PATH.'/admin/configuration/logadmin.php';
}
elseif ($okt->page->action == 'router' && $okt->checkPerm('is_superadmin')) {
	require OKT_INC_PATH.'/admin/configuration/router.php';
}
elseif ($okt->page->action == 'advanced' && $okt->checkPerm('is_superadmin')) {
	require OKT_INC_PATH.'/admin/configuration/advanced.php';
}
else {
	$okt->redirect('index.php');
}
