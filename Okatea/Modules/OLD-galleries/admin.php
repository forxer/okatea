<?php
/**
 * @ingroup okt_module_galleries
 * @brief La page d'administration.
 *
 */

# Accès direct interdit
if (! defined('ON_MODULE'))
	die();

if (! $okt->checkPerm('galleries'))
{
	http::redirect(OKT_ADMIN_LOGIN_PAGE);
}

# suppression d'un élément
if ($okt->page->action === 'delete' && ! empty($_GET['item_id']) && $okt->checkPerm('galleries_remove'))
{
	if ($okt->galleries->items->deleteItem($_GET['item_id']))
	{
		http::redirect('module.php?m=galleries&amp;action=index&amp;deleted=1');
	}
	else
	{
		$okt->page->action = 'index';
	}
}

# button set
$okt->page->setButtonset('galleriesBtSt', array(
	'id' => 'galleries-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array()
));

# title tag
$okt->page->addTitleTag($okt->galleries->getTitle());

# fil d'ariane
$okt->page->addAriane($okt->galleries->getName(), 'module.php?m=galleries');

# inclusion du fichier requis en fonction de l'action demandée
if (! $okt->page->action || $okt->page->action === 'index')
{
	require __DIR__ . '/admin/index.php';
}
elseif ($okt->page->action === 'gallery')
{
	require __DIR__ . '/admin/gallery.php';
}
elseif ($okt->page->action === 'items')
{
	require __DIR__ . '/admin/items.php';
}
elseif ($okt->page->action === 'edit')
{
	require __DIR__ . '/admin/item.php';
}
elseif ($okt->page->action === 'add' && $okt->checkPerm('galleries_add'))
{
	require __DIR__ . '/admin/item.php';
}
elseif ($okt->page->action === 'add_zip' && $okt->galleries->config->enable_zip_upload && $okt->checkPerm('galleries_add'))
{
	require __DIR__ . '/admin/add_zip.php';
}
elseif ($okt->page->action === 'add_multiples' && $okt->galleries->config->enable_multiple_upload && $okt->checkPerm('galleries_add') && file_exists(__DIR__ . '/admin/add_multiples/' . $okt->galleries->config->multiple_upload_type . '.php'))
{
	require __DIR__ . '/admin/add_multiples/' . $okt->galleries->config->multiple_upload_type . '.php';
}
elseif ($okt->page->action === 'display' && $okt->checkPerm('galleries_display'))
{
	require __DIR__ . '/admin/display.php';
}
elseif ($okt->page->action === 'config' && $okt->checkPerm('galleries_config'))
{
	require __DIR__ . '/admin/config.php';
}
else
{
	http::redirect('index.php');
}
