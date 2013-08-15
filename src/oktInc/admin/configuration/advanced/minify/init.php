<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


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
