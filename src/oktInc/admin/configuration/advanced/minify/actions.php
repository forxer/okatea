<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Configuration avancée minify (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


if (!empty($_POST['form_sent']))
{
	$p_minify_css_admin = !empty($_POST['p_minify_css_admin']) && is_array($_POST['p_minify_css_admin']) ? array_filter(array_map('trim',$_POST['p_minify_css_admin'])) : array();
	$p_minify_js_admin = !empty($_POST['p_minify_js_admin']) && is_array($_POST['p_minify_js_admin']) ? array_filter(array_map('trim',$_POST['p_minify_js_admin'])) : array();

	$p_minify_css_public = !empty($_POST['p_minify_css_public']) && is_array($_POST['p_minify_css_public']) ? array_filter(array_map('trim',$_POST['p_minify_css_public'])) : array();
	$p_minify_js_public = !empty($_POST['p_minify_js_public']) && is_array($_POST['p_minify_js_public']) ? array_filter(array_map('trim',$_POST['p_minify_js_public'])) : array();

	$aPageData['aNewConf'] = array_merge($aPageData['aNewConf'], array(
		'minify_css_admin' => (array)$p_minify_css_admin,
		'minify_js_admin' => (array)$p_minify_js_admin,
		'minify_css_public' => (array)$p_minify_css_public,
		'minify_js_public' => (array)$p_minify_js_public
	));
}
