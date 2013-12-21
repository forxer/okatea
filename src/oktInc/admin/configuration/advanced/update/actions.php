<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Configuration avancée mises à jour (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;


if (!empty($_POST['form_sent']))
{
	$p_update_enabled = !empty($_POST['p_update_enabled']) ? true : false;
	$p_update_url = !empty($_POST['p_update_url']) ? $_POST['p_update_url'] : '';
	$p_update_type = !empty($_POST['p_update_type']) && $_POST['p_update_type'] == 'dev' ? 'dev' : 'stable';

	$aPageData['aNewConf'] = array_merge($aPageData['aNewConf'], array(
		'update_enabled' => (boolean)$p_update_enabled,
		'update_url' => $p_update_url,
		'update_type' => $p_update_type
	));
}
