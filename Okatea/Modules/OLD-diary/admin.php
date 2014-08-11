<?php
/**
 * @ingroup okt_module_diary
 * @brief
 *
 */

# Accès direct interdit
if (!defined('ON_MODULE'))
	die();
	
	# Perm ?
if (!$okt['visitor']->checkPerm('diary'))
{
	http::redirect(OKT_ADMIN_LOGIN_PAGE);
}

# suppression d'un élément
if ($okt->page->action === 'delete' && !empty($_GET['event_id']) && $okt['visitor']->checkPerm('diary_remove'))
{
	if ($okt->diary->delEvent($_GET['event_id']))
	{
		$okt['flashMessages']->success(__('m_diary_confirm_deleted'));
		
		http::redirect('module.php?m=diary&action=index');
	}
	else
	{
		$okt->page->action = 'index';
	}
}

# button set
$okt->page->setButtonset('diaryBtSt', array(
	'id' => 'diary-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => ($okt->page->action !== 'add') && $okt['visitor']->checkPerm('diary_add'),
			'title' => __('m_diary_add_event'),
			'url' => 'module.php?m=diary&amp;action=add',
			'ui-icon' => 'plusthick',
			'active' => ($okt->page->action === 'add')
		)
	)
));

# title tag
$okt->page->addTitleTag($okt->diary->getTitle());

# fil d'ariane
$okt->page->addAriane($okt->diary->getName(), 'module.php?m=diary');

# inclusion du fichier requis en fonction de l'action demandée
if (!$okt->page->action || $okt->page->action === 'index')
{
	require __DIR__ . '/admin/index.php';
}
elseif ($okt->page->action === 'add' && $okt['visitor']->checkPerm('diary_add'))
{
	require __DIR__ . '/admin/event.php';
}
elseif ($okt->page->action === 'edit')
{
	require __DIR__ . '/admin/event.php';
}
elseif ($okt->page->action === 'display' && $okt['visitor']->checkPerm('diary_display'))
{
	require __DIR__ . '/admin/display.php';
}
elseif ($okt->page->action === 'config' && $okt['visitor']->checkPerm('diary_config'))
{
	require __DIR__ . '/admin/config.php';
}
else
{
	http::redirect('index.php');
}
