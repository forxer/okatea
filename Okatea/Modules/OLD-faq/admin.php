<?php
/**
 * @ingroup okt_module_faq
 * @brief La page d'administration du module faq
 *
 */

# Accès direct interdit
if (!defined('ON_MODULE'))
	die();
	
	# Perms ?
if (!$okt['visitor']->checkPerm('faq'))
{
	http::redirect(OKT_ADMIN_LOGIN_PAGE);
}

# suppression d'une question
if ($okt->page->action === 'delete' && !empty($_GET['questions_id']) && $okt['visitor']->checkPerm('faq_remove'))
{
	if ($okt->faq->deleteQuestion($_GET['questions_id']))
	{
		$okt['flashMessages']->success(__('m_faq_question_deleted'));
		
		http::redirect('module.php?m=faq&action=index');
	}
	else
	{
		$okt->page->action = 'index';
	}
}

# title tag
$okt->page->addTitleTag($okt->faq->getTitle());

# fil d'ariane
$okt->page->addAriane($okt->faq->getName(), 'module.php?m=faq');

# button set
$okt->page->setButtonset('faqBtSt', array(
	'id' => 'faq-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => ($okt->page->action !== 'add') && $okt['visitor']->checkPerm('faq_add'),
			'title' => __('m_faq_add_question'),
			'url' => 'module.php?m=faq&amp;action=add',
			'ui-icon' => 'plusthick',
			'active' => ($okt->page->action === 'add')
		)
	)
));

# inclusion du fichier requis en fonction de l'action demandée
if ($okt->page->action === 'add' && $okt['visitor']->checkPerm('faq_add'))
{
	require __DIR__ . '/admin/question.php';
}
elseif ($okt->page->action === 'edit')
{
	require __DIR__ . '/admin/question.php';
}
elseif ($okt->page->action === 'categories' && $okt['visitor']->checkPerm('faq_categories'))
{
	require __DIR__ . '/admin/categories.php';
}
elseif ($okt->page->action === 'display' && $okt['visitor']->checkPerm('faq_display'))
{
	require __DIR__ . '/admin/display.php';
}
elseif ($okt->page->action === 'config' && $okt['visitor']->checkPerm('faq_config'))
{
	require __DIR__ . '/admin/config.php';
}
else
{
	require __DIR__ . '/admin/index.php';
}
