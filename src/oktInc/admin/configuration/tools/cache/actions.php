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


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;

# Suppression d'un fichier cache
if (!empty($_GET['cache_file']) && in_array($_GET['cache_file'],$aCacheFiles))
{
	if (is_dir(OKT_CACHE_PATH.'/'.$_GET['cache_file'])) {
		files::deltree(OKT_CACHE_PATH.'/'.$_GET['cache_file']);
	}
	else {
		unlink(OKT_CACHE_PATH.'/'.$_GET['cache_file']);
	}

	$okt->redirect('configuration.php?action=tools&file_deleted=1');
}


# Suppression d'un fichier cache public
if (!empty($_GET['public_cache_file']) && in_array($_GET['public_cache_file'],$aPublicCacheFiles))
{
	if (is_dir(OKT_PUBLIC_PATH.'/cache/'.$_GET['public_cache_file'])) {
		files::deltree(OKT_PUBLIC_PATH.'/cache/'.$_GET['public_cache_file']);
	}
	else {
		unlink(OKT_PUBLIC_PATH.'/cache/'.$_GET['public_cache_file']);
	}

	$okt->redirect('configuration.php?action=tools&file_deleted=1');
}

# Suppression des fichiers cache
if (!empty($_GET['all_cache_file']))
{
	util::deleteOktCacheFiles();

	util::deleteOktPublicCacheFiles();

	$okt->redirect('configuration.php?action=tools&files_deleted=1');
}
