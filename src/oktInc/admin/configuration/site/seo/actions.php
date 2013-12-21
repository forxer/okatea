<?php
/**
 * Configuration du site référencement (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;

if (!empty($_POST['form_sent']))
{
	$p_title_tag = !empty($_POST['p_title_tag']) && is_array($_POST['p_title_tag'])  ? $_POST['p_title_tag'] : array();
	$p_meta_description = !empty($_POST['p_meta_description']) && is_array($_POST['p_meta_description'])  ? $_POST['p_meta_description'] : array();
	$p_meta_keywords = !empty($_POST['p_meta_keywords']) && is_array($_POST['p_meta_keywords'])  ? $_POST['p_meta_keywords'] : array();

	$aPageData['aNewConf'] = array_merge($aPageData['aNewConf'], array(
		'title_tag' 		 => $p_title_tag,
		'meta_description' 	 => $p_meta_description,
		'meta_keywords' 	 => $p_meta_keywords
	));
}
