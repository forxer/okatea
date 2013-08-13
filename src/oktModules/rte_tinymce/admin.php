<?php
/**
 * @ingroup okt_module_rte_tinyMCE
 * @brief La page d'administration.
 *
 */

# Accès direct interdit
if (!defined('ON_RTE_TINYMCE_MODULE')) die;

# Perm ?
//if (!$okt->checkPerm('rte_tinymce')) {
//	$okt->redirect(OKT_ADMIN_LOGIN_PAGE);
//}

# inclusion du fichier requis en fonction de l'action demandée
//if (!$okt->page->action || $okt->page->action === 'index') {
//	require dirname(__FILE__).'/inc/admin/index.php';
//}
if ($okt->page->action === 'config' && $okt->checkPerm('rte_tinymce_config')) {
	require dirname(__FILE__).'/inc/admin/config.php';
}
else {
	$okt->redirect('index.php');
}
