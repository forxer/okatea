<?php
/**
 * @ingroup okt_module_rte_tinyMCE_3
 * @brief La page d'administration.
 *
 */

# Accès direct interdit
if (!defined('ON_RTE_TINYMCE_3_MODULE')) die;

# Perm ?
//if (!$okt->checkPerm('rte_tinymce_3')) {
//	http::redirect(OKT_ADMIN_LOGIN_PAGE);
//}

# inclusion du fichier requis en fonction de l'action demandée
//if (!$okt->page->action || $okt->page->action === 'index') {
//	require __DIR__.'/admin/index.php';
//}
if ($okt->page->action === 'config' && $okt->checkPerm('rte_tinymce_3_config')) {
	require __DIR__.'/admin/config.php';
}
else {
	http::redirect('index.php');
}
