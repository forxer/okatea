<?php
/**
 * @ingroup okt_module_diary
 * @brief
 *
 */



# Accès direct interdit
if (!defined('ON_DIARY_MODULE')) die;


# Perm ?
if (!$okt->checkPerm('diary')) {
	$okt->redirect(OKT_ADMIN_LOGIN_PAGE);
}


# suppression d'un élément
if ($okt->page->action === 'delete' && !empty($_GET['event_id']) && $okt->checkPerm('diary_remove'))
{
	if ($okt->diary->delEvent($_GET['event_id'])) {
		$okt->redirect('module.php?m=diary&action=index&deleted=1');
	}
	else {
		$okt->page->action = 'index';
	}
}


# button set
$okt->page->setButtonset('diaryBtSt',array(
	'id' => 'diary-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => ($okt->page->action !== 'add') && $okt->checkPerm('diary_add'),
			'title' => __('m_diary_add_event'),
			'url' => 'module.php?m=diary&amp;action=add',
			'ui-icon' => 'plusthick',
			'active' => ($okt->page->action === 'add'),
		)
	)
));


# title tag
$okt->page->addTitleTag($okt->diary->getTitle());

# fil d'ariane
$okt->page->addAriane($okt->diary->getName(),'module.php?m=diary');


# inclusion du fichier requis en fonction de l'action demandée
if (!$okt->page->action || $okt->page->action === 'index') {
	require dirname(__FILE__).'/inc/admin/index.php';
}
elseif ($okt->page->action === 'add' && $okt->checkPerm('diary_add')) {
	require dirname(__FILE__).'/inc/admin/event.php';
}
elseif ($okt->page->action === 'edit') {
	require dirname(__FILE__).'/inc/admin/event.php';
}
elseif ($okt->page->action === 'display' && $okt->checkPerm('diary_display')) {
	require dirname(__FILE__).'/inc/admin/display.php';
}
elseif ($okt->page->action === 'config' && $okt->checkPerm('diary_config')) {
	require dirname(__FILE__).'/inc/admin/config.php';
}
else {
	$okt->redirect('index.php');
}
