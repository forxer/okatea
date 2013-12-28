<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Fichier de configuration des menus de navigation
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;


# locales
$okt->l10n->loadFile($okt->options->locales_dir.'/'.$okt->user->language.'/admin.navigation');


# titre et fil d'ariane
$okt->page->addGlobalTitle(__('c_a_config_navigation'), 'configuration.php?action=navigation');


# inclusion du fichier requis en fonction de l'action demandée
if (!$okt->page->do || $okt->page->do === 'index') {
	require __DIR__.'/navigation/index.php';
}
elseif ($okt->page->do === 'menu') {
	require __DIR__.'/navigation/menu.php';
}
elseif ($okt->page->do === 'items') {
	require __DIR__.'/navigation/items.php';
}
elseif ($okt->page->do === 'item') {
	require __DIR__.'/navigation/item.php';
}
elseif ($okt->page->do === 'config') {
	require __DIR__.'/navigation/config.php';
}
else {
	http::redirect('index.php');
}
