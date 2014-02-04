<?php
##header##

use Okatea\Tao\Misc\Utilities;
use Okatea\Website\Pager;

# inclusion du preprend public général
require_once __DIR__.'/../../../../oktInc/public/prepend.php';


# module actuel
$okt->page->module = '##module_id##';
$okt->page->action = 'list';


# initialisation paramètres
$aItemsParams = array(
	'visibility' => 1
);


# initialisation des filtres
$okt->##module_id##->filtersStart('public');


# ré-initialisation filtres
if (!empty($_GET['init_##module_id##_filters']))
{
	$okt->##module_id##->filters->initFilters();
	http::redirect($okt->##module_id##->config->url);
}


# paramètres personnalisés
if (!empty($aItemsCustomParams) && is_array($aItemsCustomParams)) {
	$aItemsParams = array_merge($aItemsParams,$aItemsCustomParams);
}


# initialisation des filtres
$okt->##module_id##->filters->setParams($aItemsParams);
$okt->##module_id##->filters->getFilters();


# initialisation de la pagination
$iNumFilteredItems = $okt->##module_id##->getItems($aItemsParams,true);

$oItemsPager = new Pager($okt, $okt->##module_id##->filters->params->page, $iNumFilteredItems, $okt->##module_id##->filters->params->nb_per_page);

$iNumPages = $oItemsPager->getNbPages();

$okt->##module_id##->filters->normalizePage($iNumPages);

$aItemsParams['limit'] = (($okt->##module_id##->filters->params->page-1)*$okt->##module_id##->filters->params->nb_per_page).','.$okt->##module_id##->filters->params->nb_per_page;


# récupération des éléments
$rsItemsList = $okt->##module_id##->getItems($aItemsParams);


$iCountLine = 0;
while ($rsItemsList->fetch())
{
	$rsItemsList->odd_even = ($iCountLine%2 == 0 ? 'even' : 'odd');
	$iCountLine++;

	$rsItemsList->url = $rsItemsList->getItemUrl();

	if (!$okt->##module_id##->config->enable_rte) {
		$rsItemsList->description = Utilities::nlToP($rsItemsList->description);
	}

//	if ($okt->##module_id##->config->public_truncat_char > 0) {
//		$rsItemsList->description = text::cutString(strip_tags($rsItemsList->description),$okt->##module_id##->config->public_truncat_char);
//	}
}
unset($iCountLine);


# meta description
if ($okt->##module_id##->config->meta_description != '') {
	$okt->page->meta_description = $okt->##module_id##->config->meta_description;
}
else {
	$okt->page->meta_description = Utilities::getSiteMetaDesc();
}


# meta keywords
if ($okt->##module_id##->config->meta_keywords != '') {
	$okt->page->meta_keywords = $okt->##module_id##->config->meta_keywords;
}
else {
	$okt->page->meta_keywords = Utilities::getSiteMetaKeywords();
}


# début du fil d'ariane
$okt->page->breadcrumb->add($okt->##module_id##->getName(),$okt->##module_id##->config->url);


# ajout du numéro de page au title
if ($okt->##module_id##->filters->params->page > 1) {
	$okt->page->addTitleTag(sprintf(__('c_c_Page_%s'),$okt->##module_id##->filters->params->page));
}


# title tag du module
$okt->page->addTitleTag($okt->##module_id##->getTitle());


$rsItemsList->numPages = $iNumPages;
$rsItemsList->pager = $oItemsPager;
