<?php
/**
 * Configuration du site générale (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;

if (!empty($_POST['form_sent']))
{
	$p_title = !empty($_POST['p_title']) && is_array($_POST['p_title']) ? $_POST['p_title'] : array();
	$p_desc = !empty($_POST['p_desc']) && is_array($_POST['p_desc'])  ? $_POST['p_desc'] : array();
	$p_cnil_number = !empty($_POST['p_cnil_number']) ? $_POST['p_cnil_number'] : '';

	$aPageData['aNewConf'] = array_merge($aPageData['aNewConf'], array(
		'title' 			 => $p_title,
		'desc' 				 => $p_desc,
		'cnil_number'		 => $p_cnil_number
	));
}
