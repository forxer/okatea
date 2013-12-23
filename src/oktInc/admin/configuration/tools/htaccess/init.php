<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Outil gestion du .htaccess (partie initialisation)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;


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
