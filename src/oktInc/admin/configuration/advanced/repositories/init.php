<?php
/**
 * Configuration avancée dépôts (partie initialisation)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;

$aModulesRepositories = (array)$okt->config->modules_repositories;
$aThemesRepositories = (array)$okt->config->themes_repositories;
