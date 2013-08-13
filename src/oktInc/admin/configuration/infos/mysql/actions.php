<?php
/**
 * Outil gestion MySQL (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


# optimisation d’une table
if (!empty($_GET['optimize']))
{
	if ($okt->db->optimize($_GET['optimize']) === false) {
		$okt->error->set($okt->db->error());
	}

	$okt->redirect('configuration.php?action=infos&table_optimized=1');
}

# vidange d’une table
if (!empty($_GET['truncate']))
{
	if ($okt->db->execute('TRUNCATE `'.$_GET['truncate'].'`') === false) {
		$okt->error->set($okt->db->error());
	}

	$okt->redirect('configuration.php?action=infos&table_truncated=1');
}

# suppression d’une table
if (!empty($_GET['drop']))
{
	if ($okt->db->execute('DROP TABLE `'.$_GET['drop'].'`') === false) {
		$okt->error->set($okt->db->error());
	}

	$okt->redirect('configuration.php?action=infos&table_droped=1');
}
