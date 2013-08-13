<?php
/**
 * Outil de nettoyage (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;

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

		$iNumProcessed = util::recursiveCleanup(OKT_ROOT_PATH,$aToDelete);
		$okt->redirect('configuration.php?action=tools&cleaned='.$iNumProcessed);
	}
}
