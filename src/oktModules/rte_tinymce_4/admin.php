<?php
/**
 * @ingroup okt_module_rte_tinyMCE_4
 * @brief La page d'administration.
 *
 */

# Accès direct interdit
if (!defined('ON_RTE_TINYMCE_4_MODULE')) die;

# Perm ?
//if (!$okt->checkPerm('rte_tinymce_4')) {
//	http::redirect(OKT_ADMIN_LOGIN_PAGE);
//}

# inclusion du fichier requis en fonction de l'action demandée
//if (!$okt->page->action || $okt->page->action === 'index') {
//	require __DIR__.'/inc/admin/index.php';
//}
if ($okt->page->action === 'config' && $okt->checkPerm('rte_tinymce_4_config')) {
	require __DIR__.'/inc/admin/config.php';
}
else {
	http::redirect('index.php');
}
