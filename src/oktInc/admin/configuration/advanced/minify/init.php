<?php
/**
 * Configuration avancée minify (partie initialisation)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;

$aMinifyCssAdmin = (array)$okt->config->minify_css_admin;
$aMinifyJsAdmin = (array)$okt->config->minify_js_admin;

$aMinifyCssPublic = (array)$okt->config->minify_css_public;
$aMinifyJsPublic = (array)$okt->config->minify_js_public;
