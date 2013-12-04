<?php
/**
 * @ingroup module_lbl_pirobox
 * @brief La page d'administration.
 *
 */

# Accès direct interdit
if (!defined('ON_LBL_PIROBOX_MODULE')) die;


# inclusion du fichier requis en fonction de l'action demandée
if ($okt->page->action === 'config' && $okt->checkPerm('pirobox_config')) {
	require __DIR__.'/inc/admin/config.php';
}
else {
	http::redirect('index.php');
}
