<?php
/**
 * @ingroup okt_module_estimate
 * @brief La page d'administration
 *
 */

# Accès direct interdit
if (!defined('ON_ESTIMATE_MODULE')) die;


# Perms ?
if (!$okt->checkPerm('estimate')) {
	$okt->redirect(OKT_ADMIN_LOGIN_PAGE);
}

# title tag
$okt->page->addTitleTag(__('m_estimate_main_title'));


# fil d'ariane
$okt->page->addAriane(__('m_estimate_main_title'), 'module.php?m=estimate');


# Test si le module users est installé
if (!$okt->modules->moduleExists('users'))
{
	$okt->page->flashMessages->addError(__('m_estimate_mod_users_exist'));

	$okt->redirect('index.php');
}


# Suppression d'une demande de devis
if ($okt->page->action === 'delete' && !empty($_GET['estimate_id']))
{
	try
	{
		$okt->estimate->deleteEstimate($_GET['estimate_id']);

		# log admin
		$okt->logAdmin->warning(array(
			'code' => 42,
			'component' => 'estimate',
			'message' => 'estimate #'.$_GET['estimate_id']
		));

		$okt->page->flashMessages->addSuccess(__('m_estimate_estimate_deleted'));

		$okt->redirect('module.php?m=estimate&action=index');
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
		$okt->page->action = 'index';
	}
}


# inclusion du fichier requis en fonction de l'action demandée
if (!$okt->page->action || $okt->page->action === 'index') {
	require __DIR__.'/inc/admin/index.php';
}
elseif ($okt->page->action === 'details') {
	require __DIR__.'/inc/admin/details.php';
}
elseif ($okt->page->action === 'products' && $okt->checkPerm('estimate_products')) {
	require __DIR__.'/inc/admin/products.php';
}
elseif ($okt->page->action === 'product' && $okt->checkPerm('estimate_products')) {
	require __DIR__.'/inc/admin/product.php';
}
elseif ($okt->page->action === 'accessories' && $okt->estimate->config->enable_accessories && $okt->checkPerm('estimate_accessories')) {
	require __DIR__.'/inc/admin/accessories.php';
}
elseif ($okt->page->action === 'accessory' && $okt->estimate->config->enable_accessories && $okt->checkPerm('estimate_accessories')) {
	require __DIR__.'/inc/admin/accessory.php';
}
elseif ($okt->page->action === 'config' && $okt->checkPerm('estimate_config')) {
	require __DIR__.'/inc/admin/config.php';
}
else {
	$okt->redirect('index.php');
}
