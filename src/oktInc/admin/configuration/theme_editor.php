<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * La page de l'éditeur de theme
 *
 * @addtogroup Okatea
 *
 */

use Tao\Themes\Editor\Editor as ThemesEditor;

# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;
define('ON_THEME_EDITOR',true);


if (!empty($_REQUEST['new_file'])) {
	require __DIR__.'/theme_editor/new_file.php';
}
else if (!empty($_REQUEST['new_template']))
{
	$okt->l10n->loadFile(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.theme.editor');

	$sThemeId = !empty($_REQUEST['theme']) ? $_REQUEST['theme'] : null;

	$sBasicTemplate = !empty($_REQUEST['basic_template']) ? rawurldecode($_REQUEST['basic_template']) : null;

	$oThemeEditor = new ThemesEditor($okt, OKT_THEMES_DIR, OKT_THEMES_PATH);

	if ($sThemeId)
	{
		try
		{
			$oThemeEditor->loadTheme($sThemeId);
		}
		catch (Exception $e) {
			$okt->error->set($e->getMessage());
			$sThemeId = null;
		}
	}
	else {
		$okt->error->set(__('c_a_te_error_choose_theme'));
	}

	if (empty($sBasicTemplate)) {
		require __DIR__.'/theme_editor/choose_basic_template.php';
	}
	else {
		require __DIR__.'/theme_editor/new_template.php';
	}
}
else {
	require __DIR__.'/theme_editor/index.php';
}
