<?php
/**
 * @ingroup okt_module_partners
 * @brief La page d'administration du module partners
 *
 */


# Accès direct interdit
if (!defined('ON_PARTNERS_MODULE')) die;


# Perms ?
if (!$okt->checkPerm('partners')) {
	http::redirect(OKT_ADMIN_LOGIN_PAGE);
}


# suppression d'un partenaire
if ($okt->page->action === 'delete' && !empty($_GET['partner_id']) && $okt->checkPerm('partners_remove'))
{
	if ($okt->partners->deletePartner($_GET['partner_id']))
	{
		$okt->page->flashMessages->addSuccess(__('m_partners_deleted'));

		http::redirect('module.php?m=partners&action=index');
	}
	else {
		$okt->page->action = 'index';
	}
}


# title tag
$okt->page->addTitleTag($okt->partners->getTitle());


# fil d'ariane
$okt->page->addAriane($okt->partners->getName(),'module.php?m=partners');


# button set
$okt->page->setButtonset('partnersBtSt',array(
	'id' => 'partners-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => ($okt->page->action !== 'add') && $okt->checkPerm('partners_add'),
			'title' => __('m_partners_add_partner'),
			'url' => 'module.php?m=partners&amp;action=add',
			'ui-icon' => 'plusthick',
			'active' => ($okt->page->action === 'add'),
		)
	)
));


# inclusion du fichier requis en fonction de l'action demandée
if ($okt->page->action === 'add' && $okt->checkPerm('partners_add')) {
	require __DIR__.'/inc/admin/partner.php';
}
elseif ($okt->page->action === 'edit') {
	require __DIR__.'/inc/admin/partner.php';
}
elseif ($okt->page->action === 'categories' && $okt->partners->config->enable_categories && $okt->checkPerm('partners_add')) {
	require __DIR__.'/inc/admin/categories.php';
}
elseif ($okt->page->action === 'display' && $okt->checkPerm('partners')) {
	require __DIR__.'/inc/admin/display.php';
}
elseif ($okt->page->action === 'config' && $okt->checkPerm('partners_config')) {
	require __DIR__.'/inc/admin/config.php';
}
else {
	require __DIR__.'/inc/admin/index.php';
}
