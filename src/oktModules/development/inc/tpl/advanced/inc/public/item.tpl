<?php
##header##

use Tao\Misc\Utilities as util;

# inclusion du preprend public général
require_once __DIR__.'/../../../../oktInc/public/prepend.php';


# récupération de l'élément en fonction du slug
$slug = !empty($_GET['slug']) ? $_GET['slug'] : null;


# paramètres de base
$aItemParams = array(
	'slug' => $slug,
	'visibility' => 1
);


# paramètres personnalisés
if (!empty($aItemCustomParams) && is_array($aItemCustomParams)) {
	$aItemParams = array_merge($aItemParams,$aItemCustomParams);
}


# récupération de l'élément
$rsItem = $okt->##module_id##->getItems($aItemParams);

if ($rsItem->isEmpty()) {
	$okt->page->serve404();
}


# module actuel
$okt->page->module = '##module_id##';
$okt->page->action = 'item';


# meta description
if ($rsItem->meta_description != '') {
	$okt->page->meta_description = $rsItem->meta_description;
}
else if ($okt->##module_id##->config->meta_description != '') {
	$okt->page->meta_description = $okt->##module_id##->config->meta_description;
}
else {
	$okt->page->meta_description = util::getSiteMetaDesc();
}


# meta keywords
if ($rsItem->meta_keywords != '') {
	$okt->page->meta_keywords = $rsItem->meta_keywords;
}
else if ($okt->##module_id##->config->meta_keywords != '') {
	$okt->page->meta_keywords = $okt->##module_id##->config->meta_keywords;
}
else {
	$okt->page->meta_keywords = util::getSiteMetaKeywords();
}


# description
if (!$okt->##module_id##->config->enable_rte) {
	$rsItem->description = util::nlToP($rsItem->description);
}


# récupération des images
$rsItem->images = $rsItem->getImagesInfo();


# récupération des fichiers
$rsItem->files = $rsItem->getFilesInfo();


# title tag du module
$okt->page->addTitleTag($okt->##module_id##->getTitle());


# début du fil d'ariane
$okt->page->breadcrumb->add($okt->##module_id##->getName(),$okt->##module_id##->config->url);


# title tag de la page
$okt->page->addTitleTag(($rsItem->title_tag != '' ? $rsItem->title_tag : $rsItem->title));


# fil d'ariane de la page
$okt->page->breadcrumb->add($rsItem->title,$rsItem->getItemUrl());

