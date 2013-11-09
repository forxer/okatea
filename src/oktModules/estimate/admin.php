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
	$okt->error->set(__('m_estimate_mod_users_exist'));

	# En-tête
	require OKT_ADMIN_HEADER_FILE;

	echo '<p>'.__('m_estimate_mod_users_exist_details').'</p>';

	# Pied-de-page
	require OKT_ADMIN_FOOTER_FILE;

	exit;
}


# inclusion du fichier requis en fonction de l'action demandée
if (!$okt->page->action || $okt->page->action === 'index') {
	require __DIR__.'/inc/admin/index.php';
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
