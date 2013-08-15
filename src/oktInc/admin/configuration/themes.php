<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * La page de gestion des thèmes.
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;
define('OKT_THEMES_MANAGEMENT', true);

# Themes object
$oThemes = new oktThemes($okt, OKT_THEMES_PATH);


# Locales
l10n::set(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.themes');

if (!$okt->page->do || $okt->page->do === 'index') {
	require dirname(__FILE__).'/themes/index.php';
}
elseif ($okt->page->do === 'add') {
	require dirname(__FILE__).'/themes/add.php';
}
