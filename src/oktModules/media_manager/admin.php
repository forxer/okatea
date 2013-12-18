<?php
/**
 * @ingroup okt_module_media_manager
 * @brief La page d'administration.
 *
 */

# Accès direct interdit
if (!defined('ON_MEDIA_MANAGER_MODULE')) die;


# title tag
$okt->page->addTitleTag(__('Media manager'));

# fil d'ariane
$okt->page->addAriane(__('Media manager'),'module.php?m=media_manager');

# inclusion du fichier requis en fonction de l'action demandée
if ((!$okt->page->action || $okt->page->action === 'index') && ($okt->checkPerm('media') || $okt->checkPerm('media_admin'))) {
	require __DIR__.'/admin/index.php';
}
elseif ($okt->page->action === 'item' && ($okt->checkPerm('media') || $okt->checkPerm('media_admin'))) {
	require __DIR__.'/admin/item.php';
}
elseif ($okt->page->action === 'config' && $okt->checkPerm('media_config')) {
	require __DIR__.'/admin/config.php';
}
else {
	http::redirect('index.php');
}
