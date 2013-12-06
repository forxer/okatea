<?php
/**
 * @ingroup okt_module_images_sets
 * @brief
 *
 */



# Accès direct interdit
if (!defined('ON_IMAGES_SETS_MODULE')) die;


# title tag
$okt->page->addTitleTag($okt->images_sets->getTitle());

# fil d'ariane
$okt->page->addAriane($okt->images_sets->getName(),'module.php?m=images_sets');


# inclusion du fichier requis en fonction de l'action demandée
if (!$okt->page->action || $okt->page->action === 'index') {
	require __DIR__.'/inc/admin/index.php';
}
elseif ($okt->page->action === 'set') {
	require __DIR__.'/inc/admin/set.php';
}
else {
	http::redirect('index.php');
}
