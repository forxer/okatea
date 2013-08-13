<?php
/**
 * @ingroup okt_module_news
 * @brief La page d'administration
 *
 */

# Accès direct interdit
if (!defined('ON_NEWS_MODULE')) die;


if (!$okt->checkPerm('news_usage') && !$okt->checkPerm('news_contentadmin')) {
	$okt->redirect('index.php');
}


# Suppression d'un article
if ($okt->page->action === 'delete' && !empty($_GET['post_id']) && $okt->checkPerm('news_delete'))
{
	try
	{
		$okt->news->deletePost($_GET['post_id']);

		# log admin
		$okt->logAdmin->warning(array(
			'code' => 42,
			'component' => 'news',
			'message' => 'post #'.$_GET['post_id']
		));

		$okt->redirect('module.php?m=news&action=index&deleted=1');
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
		$okt->page->action = 'index';
	}
}


# button set
$okt->page->setButtonset('newsBtSt',array(
	'id' => 'news-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => ($okt->page->action !== 'add'),
			'title' => __('m_news_menu_add_post'),
			'url' => 'module.php?m=news&amp;action=add',
			'ui-icon' => 'plusthick',
			'active' => ($okt->page->action === 'add'),
		)
	)
));


# title tag
$okt->page->addTitleTag($okt->news->getTitle());


# fil d'ariane
$okt->page->addAriane($okt->news->getName(),'module.php?m=news');


# inclusion du fichier requis en fonction de l'action demandée
if (!$okt->page->action || $okt->page->action === 'index') {
	require dirname(__FILE__).'/inc/admin/index.php';
}
elseif ($okt->page->action === 'add' || $okt->page->action === 'edit') {
	require dirname(__FILE__).'/inc/admin/post.php';
}
elseif ($okt->page->action === 'categories' && $okt->news->config->categories['enable'] && $okt->checkPerm('news_categories'))
{
	if ($okt->page->do === 'add' || $okt->page->do === 'edit') {
		require dirname(__FILE__).'/inc/admin/category.php';
	}
	else {
		require dirname(__FILE__).'/inc/admin/categories.php';
	}
}
elseif ($okt->page->action === 'display' && $okt->checkPerm('news_display')) {
	require dirname(__FILE__).'/inc/admin/display.php';
}
elseif ($okt->page->action === 'config' && $okt->checkPerm('news_config')) {
	require dirname(__FILE__).'/inc/admin/config.php';
}
else {
	$okt->redirect('index.php');
}
