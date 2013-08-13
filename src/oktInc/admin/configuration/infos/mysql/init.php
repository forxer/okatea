<?php
/**
 * Outil gestion MySQL (partie initialisation)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


$table = !empty($_GET['table']) ? $_GET['table'] : null;

$okt->page->messages->success('table_truncated',__('c_a_infos_mysql_table_truncated'));
$okt->page->messages->success('table_droped',__('c_a_infos_mysql_table_droped'));
$okt->page->messages->success('table_optimized',__('c_a_infos_mysql_table_optimized'));
