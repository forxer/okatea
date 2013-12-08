<?php
/**
 * @ingroup okt_module_catalog
 * @brief "controller" pour l'affichage public d'un produit
 *
 */

use Tao\Misc\Utilities as util;

# Inclusion du preprend public général
require_once __DIR__.'/../../../../oktInc/public/prepend.php';

# Récupération de l’article en fonction du slug
$slug = !empty($_GET['slug']) ? $_GET['slug'] : null;

$product = $okt->catalog->getProds(array(
	'slug' => $slug,
	'visibility' => 1
));

if ($product->isEmpty()) {
	$okt->page->serve404();
}

# Formatage des données
if ($product->title_tag == '') {
	$product->title_tag = $product->title;
}

$product->url = $product->getProductUrl();

if (!$okt->catalog->config->rte_enable) {
	$product->content = util::nlToP($product->content);
}

$product->category_url = $product->getCategoryUrl();


# module actuel
$okt->page->module = 'catalog';
$okt->page->action = 'item';


# meta description
if ($product->meta_description != '') {
	$okt->page->meta_description = $product->meta_description;
}
else if ($okt->catalog->config->meta_description != '') {
	$okt->page->meta_description = $okt->catalog->config->meta_description;
}

else {
	$okt->page->meta_description = util::getSiteMetaDesc();
}

# meta keywords
if ($product->meta_keywords != '') {
	$okt->page->meta_keywords = $product->meta_keywords;
}
else if ($okt->catalog->config->meta_keywords != '') {
	$okt->page->meta_keywords = $okt->catalog->config->meta_keywords;
}
else {
	$okt->page->meta_keywords = util::getSiteMetaKeywords();
}


# Récupération des images
$product->images = $product->getImagesInfo();

# Récupération des fichiers
$product->files = $product->getFilesInfo();


# Début du fil d'ariane
$okt->page->breadcrumb->add($okt->catalog->getName(),$okt->catalog->config->url);

# Title tag du module
$okt->page->addTitleTag($okt->catalog->getTitle());


# Title tag de la catégorie
$okt->page->addTitleTag($product->category_name);

# Title tag du produit
$okt->page->addTitleTag($product->title_tag);

# Ajout de la hiérarchie des catégories au fil d'ariane
if ($okt->catalog->config->categories_enable && $product->category_id)
{
	$rsPath = $okt->catalog->getPath($product->category_id,true);
	while ($rsPath->fetch())
	{
		$okt->page->breadcrumb->add(
			$rsPath->name,
			$okt->page->getBaseUrl().$okt->catalog->config->public_catalog_url.$rsPath->slug
		);
	}
	unset($rsPath);
}

# Fil d'ariane du produit
$okt->page->breadcrumb->add($product->title,$product->url);

