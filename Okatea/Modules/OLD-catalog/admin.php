<?php
/**
 * @ingroup okt_module_catalog
 * @brief La page d'administration.
 *
 */

# Accès direct interdit
if (! defined('ON_MODULE'))
	die();
	
	# Perms ?
if (! $okt->checkPerm('catalog'))
{
	http::redirect('index.php');
}

# suppression d’un produit
if ($okt->page->action === 'delete' && ! empty($_GET['product_id']))
{
	if ($okt->catalog->deleteProd($_GET['product_id']))
	{
		$okt->page->flash->success(__('Le produit a été supprimé.'));
		
		http::redirect('module.php?m=catalog&action=index');
	}
	else
	{
		$okt->page->action = 'index';
	}
}

# title tag
$okt->page->addTitleTag($okt->catalog->getTitle());

# fil d'ariane
$okt->page->addAriane($okt->catalog->getName(), 'module.php?m=catalog');

# button set
$okt->page->setButtonset('catalogBtSt', array(
	'id' => 'actualites-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => ($okt->page->action !== 'add' && $okt->checkPerm('catalog_add')),
			'title' => 'Ajouter un produit',
			'url' => 'module.php?m=catalog&amp;action=add',
			'ui-icon' => 'plusthick',
			'active' => ($okt->page->action === 'add')
		)
	)
));

# inclusion du fichier requis en fonction de l'action demandée
if (! $okt->page->action || $okt->page->action === 'index')
{
	require __DIR__ . '/admin/index.php';
}
elseif ($okt->page->action === 'add' || $okt->page->action === 'edit')
{
	require __DIR__ . '/admin/product.php';
}
elseif ($okt->page->action === 'categories' && $okt->catalog->config->categories_enable && $okt->checkPerm('catalog_categories'))
{
	require __DIR__ . '/admin/categories.php';
}
elseif ($okt->page->action === 'display' && $okt->checkPerm('catalog_display'))
{
	require __DIR__ . '/admin/display.php';
}
elseif ($okt->page->action === 'config' && $okt->checkPerm('catalog_config'))
{
	require __DIR__ . '/admin/config.php';
}
else
{
	http::redirect('index.php');
}
