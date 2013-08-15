<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Configuration avancée debug (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


if (!empty($_POST['form_sent']))
{
	$p_stop_redirect_on_error = !empty($_POST['p_stop_redirect_on_error']) ? true : false;
	$p_debug_enabled = !empty($_POST['p_debug_enabled']) ? true : false;
	$p_xdebug_enabled = !empty($_POST['p_xdebug_enabled']) ? true : false;

	$aPageData['aNewConf'] = array_merge($aPageData['aNewConf'], array(
		'debug_enabled' => (boolean)$p_debug_enabled,
		'stop_redirect_on_error' => (boolean)$p_stop_redirect_on_error,
		'xdebug_enabled' => (boolean)$p_xdebug_enabled
	));
}
