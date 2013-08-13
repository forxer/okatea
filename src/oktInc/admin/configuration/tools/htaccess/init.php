<?php
/**
 * Outil gestion du .htaccess (partie initialisation)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


$sHtaccessContent= '';

$bHtaccessExists = false;
if (file_exists(OKT_ROOT_PATH.'/.htaccess'))
{
	$bHtaccessExists = true;
	$sHtaccessContent = file_get_contents(OKT_ROOT_PATH.'/.htaccess');
}

$bHtaccessDistExists = false;
if (file_exists(OKT_ROOT_PATH.'/.htaccess.oktDist')) {
	$bHtaccessDistExists = true;
}

$okt->page->messages->success('htaccess_created',__('c_a_tools_htaccess_created'));
$okt->page->messages->success('htaccess_edited',__('c_a_tools_htaccess_edited'));
