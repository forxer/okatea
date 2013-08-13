<?php
##header##


# Accès direct interdit
if (!defined('ON_##module_upper_id##_MODULE')) die;


# Perm ?
if (!$okt->checkPerm('##module_id##')) {
	$okt->redirect(OKT_ADMIN_LOGIN_PAGE);
}


# suppression d’un élément
if ($okt->page->action === 'delete' && !empty($_GET['item_id']) && $okt->checkPerm('##module_id##_remove'))
{
	if ($okt->##module_id##->delItem($_GET['item_id'])) {
		$okt->redirect('module.php?m=##module_id##&action=index&deleted=1');
	}
	else {
		$okt->page->action = 'index';
	}
}


# button set
$okt->page->setButtonset('##module_id##BtSt',array(
	'id' => '##module_id##-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => ($okt->page->action !== 'add') && $okt->checkPerm('##module_id##_add'),
			'title' => __('m_##module_id##_add_item'),
			'url' => 'module.php?m=##module_id##&amp;action=add',
			'ui-icon' => 'plusthick',
			'active' => ($okt->page->action === 'add'),
		)
	)
));


# title tag
$okt->page->addTitleTag($okt->##module_id##->getTitle());

# fil d'ariane
$okt->page->addAriane($okt->##module_id##->getName(),'module.php?m=##module_id##');


# inclusion du fichier requis en fonction de l'action demandée
if (!$okt->page->action || $okt->page->action === 'index') {
	require dirname(__FILE__).'/inc/admin/index.php';
}
elseif ($okt->page->action === 'add' && $okt->checkPerm('##module_id##_add')) {
	require dirname(__FILE__).'/inc/admin/item.php';
}
elseif ($okt->page->action === 'edit') {
	require dirname(__FILE__).'/inc/admin/item.php';
}
elseif ($okt->page->action === 'display' && $okt->checkPerm('##module_id##_display')) {
	require dirname(__FILE__).'/inc/admin/display.php';
}
elseif ($okt->page->action === 'config' && $okt->checkPerm('##module_id##_config')) {
	require dirname(__FILE__).'/inc/admin/config.php';
}
else {
	$okt->redirect('index.php');
}
