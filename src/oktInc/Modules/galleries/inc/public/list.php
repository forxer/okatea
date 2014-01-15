<?php
/**
 * @ingroup okt_module_galleries
 * @brief "controller" pour l'affichage public d'une liste de galeries
 *
 */


# inclusion du preprend public général
require_once __DIR__.'/../../../../oktInc/public/prepend.php';


# Récupération de la liste des galeries à la racine
$rsGalleriesList = $okt->galleries->tree->getGalleries(array(
	'active' => 1,
	'parent_id' => 0
));


# module actuel
$okt->page->module = 'galleries';
$okt->page->action = 'list';


# meta description
if ($okt->galleries->config->meta_description != '') {
	$okt->page->meta_description = $okt->galleries->config->meta_description;
}
else {
	$okt->page->meta_description = Utilities::getSiteMetaDesc();
}


# meta keywords
if ($okt->galleries->config->meta_keywords != '') {
	$okt->page->meta_keywords = $okt->galleries->config->meta_keywords;
}
else {
	$okt->page->meta_keywords = Utilities::getSiteMetaKeywords();
}


# début du fil d'ariane
$okt->page->breadcrumb->add($okt->galleries->getName(),$okt->galleries->config->url);


# title tag du module
$okt->page->addTitleTag($okt->galleries->getTitle());

