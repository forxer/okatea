<?php
/**
 * @ingroup okt_module_lbl_nyromodal_2
 * @brief La page d'administration.
 *
 */

# Accès direct interdit
if (!defined('ON_LBL_NYROMODAL_2_MODULE')) die;


# inclusion du fichier requis en fonction de l'action demandée
if ($okt->page->action === 'config' && $okt->checkPerm('nyromodal_2_config')) {
	require dirname(__FILE__).'/inc/admin/config.php';
}
else {
	$okt->redirect('index.php');
}
