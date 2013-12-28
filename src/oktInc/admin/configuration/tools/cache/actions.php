<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Outil gestion du cache (partie traitements)
 *
 * @addtogroup Okatea
 *
 */

use Tao\Misc\Utilities as util;

# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;

# Suppression d'un fichier cache
if (!empty($_GET['cache_file']) && in_array($_GET['cache_file'], $aCacheFiles))
{
	if (is_dir($okt->options->get('cache_dir').'/'.$_GET['cache_file'])) {
		files::deltree($okt->options->get('cache_dir').'/'.$_GET['cache_file']);
	}
	else {
		unlink($okt->options->get('cache_dir').'/'.$_GET['cache_file']);
	}

	$okt->page->flash->success(__('c_a_tools_cache_confirm'));

	http::redirect('configuration.php?action=tools');
}


# Suppression d'un fichier cache public
if (!empty($_GET['public_cache_file']) && in_array($_GET['public_cache_file'], $aPublicCacheFiles))
{
	if (is_dir($okt->options->public_dir.'/cache/'.$_GET['public_cache_file'])) {
		files::deltree($okt->options->public_dir.'/cache/'.$_GET['public_cache_file']);
	}
	else {
		unlink($okt->options->public_dir.'/cache/'.$_GET['public_cache_file']);
	}

	$okt->page->flash->success(__('c_a_tools_cache_confirm'));

	http::redirect('configuration.php?action=tools');
}

# Suppression des fichiers cache
if (!empty($_GET['all_cache_file']))
{
	util::deleteOktCacheFiles();

	util::deleteOktPublicCacheFiles();

	$okt->page->flash->success(__('c_a_tools_cache_confirms'));

	http::redirect('configuration.php?action=tools');
}
