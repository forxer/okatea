<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Outil de nettoyage (partie traitements)
 *
 * @addtogroup Okatea
 *
 */

use Tao\Misc\Utilities as util;

# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;

# suppression des fichiers
if (!empty($_POST['cleanup']))
{
	$aToDelete = array();

	foreach ($_POST['cleanup'] as $cleanup)
	{
		if (isset($aCleanableFiles[$cleanup])) {
			$aToDelete[] = $aCleanableFiles[$cleanup];
		}
	}

	if (!empty($aToDelete))
	{
		@ini_set('memory_limit',-1);
		set_time_limit(480);

		$iNumProcessed = util::recursiveCleanup($okt->options->getRootPath(),$aToDelete);
		http::redirect('configuration.php?action=tools&cleaned='.$iNumProcessed);
	}
}
