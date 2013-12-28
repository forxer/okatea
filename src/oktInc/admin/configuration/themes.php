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

use Tao\Themes\Collection as ThemesCollection;

# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;
define('OKT_THEMES_MANAGEMENT', true);

# Themes object
$oThemes = new ThemesCollection($okt, $okt->options->get('themes_dir'));


# Locales
$okt->l10n->loadFile($okt->options->locales_dir.'/'.$okt->user->language.'/admin.themes');

if (!$okt->page->do || $okt->page->do === 'index') {
	require __DIR__.'/themes/index.php';
}
elseif ($okt->page->do === 'add') {
	require __DIR__.'/themes/add.php';
}
