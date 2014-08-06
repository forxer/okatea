<?php
/**
 * @ingroup okt_module_lbl_fancybox
 * @brief La page d'administration.
 *
 */

# Accès direct interdit
if (! defined('ON_MODULE'))
	die();
	
	# inclusion du fichier requis en fonction de l'action demandée
if ($okt->page->action === 'config' && $okt['visitor']->checkPerm('fancybox_config'))
{
	require __DIR__ . '/admin/config.php';
}
else
{
	http::redirect('index.php');
}
