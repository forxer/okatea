<?php
/**
 * @ingroup okt_module_catalog
 * @brief "controller" pour l'affichage public des produits
 *
 */

use Tao\Utils as util;

# inclusion du preprend public général
require_once __DIR__.'/../../../../oktInc/public/prepend.php';


# initialisation des paramètres
$aProductsParams = array(
	'visibility' => 1,
	'search' => !empty($_REQUEST['search']) ? $_REQUEST['search'] : null
);

# page spéciale ?
/*if (!empty($_REQUEST['promo_only']))
{
	$okt->page->action = 'promo';

	$aProductsParams = array(
		'visibility' => 1,
		'promo_only' => true
	);
}
elseif (!empty($_REQUEST['nouvo_only']))
{
	$okt->page->action = 'nouvo';

	$aProductsParams = array(
		'visibility' => 1,
		'nouvo_only' => true
	);
}
elseif (!empty($_REQUEST['favo_only']))
{
	$okt->page->action = 'favo';

	$aProductsParams = array(
		'visibility' => 1,
		'favo_only' => true
	);
}*/

# récupération de la rubrique en fonction du slug
$slug = !empty($_GET['slug']) ? $_GET['slug'] : null;

if ($okt->catalog->config->categories_enable && !is_null($slug))
{
	$rsCategory = $okt->catalog->getCategories(array('slug'=>$slug,'visibility'=>1));

	if ($rsCategory->isEmpty()) {
		$okt->page->serve404();
	}

	$aProductsParams['category_id'] = $rsCategory->id;
}


# initialisation des filtres
$okt->catalog->filtersStart();


# ré-initialisation filtres
if (!empty($_GET['catalog_init_filters']))
{
	$okt->catalog->filters->initFilters();
	http::redirect($okt->catalog->config->url);
}


# paramètres personnalisés
if (!empty($aProductsCustomParams) && is_array($aProductsCustomParams)) {
	$aProductsParams = array_merge($aProductsParams,$aProductsCustomParams);
}


# initialisation des filtres
$okt->catalog->filters->setCatalogParams($aProductsParams);
$okt->catalog->filters->getFilters();


# initialisation de la pagination
$num_filtered_products = $okt->catalog->getProds($aProductsParams,true);

$oProductsPager = new publicPager($okt->catalog->filters->params->page, $num_filtered_products, $okt->catalog->filters->params->nb_per_page);

$iNumPages = $oProductsPager->getNbPages();

$okt->catalog->filters->normalizePage($iNumPages);

$aProductsParams['limit'] = (($okt->catalog->filters->params->page-1)*$okt->catalog->filters->params->nb_per_page).','.$okt->catalog->filters->params->nb_per_page;



# récupération des produits
$productsList = $okt->catalog->getProds($aProductsParams);

$count_line = 0;
while ($productsList->fetch())
{
	$productsList->odd_even = ($count_line%2 == 0 ? 'even' : 'odd');
	$count_line++;

	$productsList->url = $productsList->getProductUrl();

	if (!$okt->catalog->config->rte_enable) {
		$productsList->content = util::nlToP($productsList->content);
	}

	if ($okt->catalog->config->public_truncat_char > 0 )
	{
		$productsList->content = html::clean($productsList->content);
		$productsList->content = text::cutString($productsList->content,$okt->catalog->config->public_truncat_char);
	}

	$productsList->category_url = $productsList->getCategoryUrl();
}
unset($count_line);


# module actuel
$okt->page->module = 'catalog';
$okt->page->action = 'list';


# meta description
if ($okt->catalog->config->meta_description != '') {
	$okt->page->meta_description = $okt->catalog->config->meta_description;
}
else {
	$okt->page->meta_description = util::getSiteMetaDesc();
}

# meta keywords
if ($okt->catalog->config->meta_keywords != '') {
	$okt->page->meta_keywords = $okt->catalog->config->meta_keywords;
}
else {
	$okt->page->meta_keywords = util::getSiteMetaKeywords();
}


# début du fil d'ariane
$okt->page->breadcrumb->add($okt->catalog->getName(),$okt->catalog->config->url);


# ajout du numéro de page au title
if ($okt->catalog->filters->params->page > 1) {
	$okt->page->addTitleTag(sprintf(__('c_c_Page_%s'),$okt->catalog->filters->params->page));
}


# title tag du module
$okt->page->addTitleTag($okt->catalog->getTitle());


# ajout de la hiérarchie des catégories au fil d'ariane et au title tag
if (!is_null($slug))
{
	$rsPath = $okt->catalog->getPath($rsCategory->id,true);

	while ($rsPath->fetch())
	{
		$okt->page->addTitleTag($rsPath->name);

		$okt->page->breadcrumb->add(
			$rsPath->name,
			$okt->page->getBaseUrl().$okt->catalog->config->public_catalog_url.$rsPath->slug
		);
	}
	unset($rsPath);
}


$productsList->numPages = $iNumPages;
$productsList->pager = $oProductsPager;

