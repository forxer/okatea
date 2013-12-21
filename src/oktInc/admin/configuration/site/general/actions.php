<?php
/**
 * Configuration du site générale (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;

if (!empty($_POST['form_sent']))
{
	$p_title = !empty($_POST['p_title']) && is_array($_POST['p_title']) ? $_POST['p_title'] : array();
	$p_desc = !empty($_POST['p_desc']) && is_array($_POST['p_desc'])  ? $_POST['p_desc'] : array();

	foreach ($p_title as $sLanguageCode=>$sTitle)
	{
		if (empty($sTitle))
		{
			if ($okt->languages->unique) {
				$okt->error->set(__('c_a_config_please_enter_website_title'));
			}
			else {
				$okt->error->set(sprintf(__('c_a_config_please_enter_website_title_in_%s'), $okt->languages->list[$sLanguageCode]['title']));
			}
		}
	}

	$aPageData['aNewConf'] = array_merge($aPageData['aNewConf'], array(
		'title' 			 => $p_title,
		'desc' 				 => $p_desc
	));
}
