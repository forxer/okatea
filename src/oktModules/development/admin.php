<?php
/**
 * @ingroup okt_module_development
 * @brief La page d'administration du module développement
 *
 */


# Accès direct interdit
if (!defined('ON_DEVELOPMENT_MODULE')) die;

# title tag
$okt->page->addTitleTag(__('Development'));

# fil d'ariane
$okt->page->addAriane(__('Development'),'module.php?m=development');


# inclusion du fichier requis en fonction de l'action demandée
if ((!$okt->page->action || $okt->page->action === 'index')) {
	require dirname(__FILE__).'/inc/admin/index.php';
}
elseif ($okt->page->action === 'debug_bar' && $okt->checkPerm('development_debug_bar')) {
	require dirname(__FILE__).'/inc/admin/debug_bar.php';
}
elseif ($okt->page->action === 'bootstrap' && $okt->checkPerm('development_bootstrap')) {
	require dirname(__FILE__).'/inc/admin/bootstrap.php';
}
elseif ($okt->page->action === 'counting' && $okt->checkPerm('development_counting')) {
	require dirname(__FILE__).'/inc/admin/counting.php';
}
else {
	$okt->redirect('index.php');
}
