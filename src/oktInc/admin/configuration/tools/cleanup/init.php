<?php
/**
 * Outil de nettoyage (partie initialisation)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


# liste des fichiers supprimables
$aCleanableFiles = array(
	1 => 'Thumbs.db',
	2 => '_notes',
	3 => '.svn'
);


# messages de confirmation
if (isset($_GET['cleaned'])) {
	$okt->page->messages->set(sprintf(__('c_a_tools_cleanup_%s_cleaned'),$_GET['cleaned']));
}

$okt->page->loader('.lazy-load');
