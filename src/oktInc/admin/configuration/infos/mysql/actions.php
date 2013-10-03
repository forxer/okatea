<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


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

	$okt->page->flashMessages->addSuccess(__('c_a_infos_mysql_table_optimized'));

	$okt->redirect('configuration.php?action=infos');
}

# vidange d’une table
if (!empty($_GET['truncate']))
{
	if ($okt->db->execute('TRUNCATE `'.$_GET['truncate'].'`') === false) {
		$okt->error->set($okt->db->error());
	}

	$okt->page->flashMessages->addSuccess(__('c_a_infos_mysql_table_truncated'));

	$okt->redirect('configuration.php?action=infos');
}

# suppression d’une table
if (!empty($_GET['drop']))
{
	if ($okt->db->execute('DROP TABLE `'.$_GET['drop'].'`') === false) {
		$okt->error->set($okt->db->error());
	}

	$okt->page->flashMessages->addSuccess(__('c_a_infos_mysql_table_droped'));

	$okt->redirect('configuration.php?action=infos');
}
