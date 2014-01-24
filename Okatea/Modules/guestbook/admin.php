<?php
/**
 * @ingroup okt_module_guestbook
 * @brief Page d'administration du module Guestbook
 *
 */


# Accès direct interdit
if (!defined('ON_MODULE')) die;

# Perm ?
if (!$okt->checkPerm('guestbook')) {
	http::redirect('index.php');
}

# suppression automatique des SPAM
if ($okt->guestbook->config->autodelete_spam > 0)
{
	$autodelete_before_date = date("Y-m-d H:i:s",(time()-($okt->guestbook->config->autodelete_spam*24*60*60)));

	$okt->guestbook->delSig(array(
		'is_spam' => true,
		'custom_where' => 'AND date_sign < \''.$autodelete_before_date.'\''
	));
}

# liste des affichages possibles
$show_list = array(
	'légitime' => 'nospam',
	'SPAM' => 'spam',
	'toutes' => 'all'
);

# Liste des langues disponibles
$aLanguagesList = array_merge(array(__('c_c_All_f') => 'all'), $okt->languages->list);

# liste des statuts possibles
$status_list = array(
	'validées' => 'validated',
	'non-validées' => 'not_validated',
	'toutes' => 'all'
);

$page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$show = (!empty($_REQUEST['show']) && in_array($_REQUEST['show'],$show_list)) ? $_REQUEST['show'] : 'nospam';
$status = (!empty($_REQUEST['status']) && in_array($_REQUEST['status'],$status_list)) ? $_REQUEST['status'] : 'all';
$language = (!empty($_REQUEST['language']) && in_array($_REQUEST['language'],$aLanguagesList)) ? $_REQUEST['language'] : 'all';

$url_params = '&amp;page='.$page.'&amp;show='.$show.'&amp;status='.$status;

# css
$okt->page->css->addFile($okt->theme->url.'/modules/guestbook/admin.css');

# title tag
$okt->page->addTitleTag($okt->guestbook->getTitle());

# fil d'ariane
$okt->page->addAriane($okt->guestbook->getName(),'module.php?m=guestbook');

# inclusion du fichier requis
if (!$okt->page->action || $okt->page->action === 'index') {
	require __DIR__.'/admin/index.php';
}
elseif ($okt->page->action === 'edit') {
	require __DIR__.'/admin/edit.php';
}
elseif ($okt->page->action === 'display' && $okt->checkPerm('guestbook_display')) {
	require __DIR__.'/admin/display.php';
}
elseif ($okt->page->action === 'config' && $okt->checkPerm('guestbook_config')) {
	require __DIR__.'/admin/config.php';
}
else {
	http::redirect('index.php');
}
