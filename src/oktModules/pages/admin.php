<?php
/**
 * @ingroup okt_module_pages
 * @brief La page d'administration
 *
 */

# Accès direct interdit
if (!defined('ON_PAGES_MODULE')) die;

if (!$okt->checkPerm('pages')) {
	$okt->redirect(OKT_ADMIN_LOGIN_PAGE);
}


# Suppression d'une page
if ($okt->page->action === 'delete' && !empty($_GET['post_id']) && $okt->checkPerm('pages_remove'))
{
	try
	{
		$okt->pages->deletePage($_GET['post_id']);

		# log admin
		$okt->logAdmin->warning(array(
			'code' => 42,
			'component' => 'pages',
			'message' => 'page #'.$_GET['post_id']
		));

		$okt->page->flashMessages->addSuccess(__('m_pages_list_page_deleted'));

		$okt->redirect('module.php?m=pages&action=index');
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
		$okt->page->action = 'index';
	}
}


# button set
$okt->page->setButtonset('pagesBtSt',array(
	'id' => 'pages-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => ($okt->page->action !== 'add') && $okt->checkPerm('pages_add'),
			'title' => __('m_pages_menu_add_page'),
			'url' => 'module.php?m=pages&amp;action=add',
			'ui-icon' => 'plusthick',
			'active' => ($okt->page->action === 'add'),
		)
	)
));


# title tag
$okt->page->addTitleTag($okt->pages->getTitle());


# fil d'ariane
$okt->page->addAriane($okt->pages->getName(),'module.php?m=pages');


# inclusion du fichier requis en fonction de l'action demandée
if (!$okt->page->action || $okt->page->action === 'index') {
	require __DIR__.'/inc/admin/index.php';
}
elseif ($okt->page->action === 'add' && $okt->checkPerm('pages_add')) {
	require __DIR__.'/inc/admin/post.php';
}
elseif ($okt->page->action === 'edit') {
	require __DIR__.'/inc/admin/post.php';
}
elseif ($okt->page->action === 'categories' && $okt->pages->config->categories['enable'] && $okt->checkPerm('pages_categories'))
{
	if ($okt->page->do === 'add' || $okt->page->do === 'edit') {
		require __DIR__.'/inc/admin/category.php';
	}
	else {
		require __DIR__.'/inc/admin/categories.php';
	}
}
elseif ($okt->page->action === 'display' && $okt->checkPerm('pages_display')) {
	require __DIR__.'/inc/admin/display.php';
}
elseif ($okt->page->action === 'config' && $okt->checkPerm('pages_config')) {
	require __DIR__.'/inc/admin/config.php';
}
else {
	$okt->redirect('index.php');
}
