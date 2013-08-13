<?php
/**
 * @ingroup okt_module_menus
 * @brief
 *
 */



# Accès direct interdit
if (!defined('ON_MENUS_MODULE')) die;


# title tag
$okt->page->addTitleTag($okt->menus->getTitle());

# fil d'ariane
$okt->page->addAriane($okt->menus->getName(),'module.php?m=menus');


# inclusion du fichier requis en fonction de l'action demandée
if (!$okt->page->action || $okt->page->action === 'index') {
	require dirname(__FILE__).'/inc/admin/index.php';
}
elseif ($okt->page->action === 'config') {
	require dirname(__FILE__).'/inc/admin/config.php';
}
else {
	$okt->redirect('index.php');
}
