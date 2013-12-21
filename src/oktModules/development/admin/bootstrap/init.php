<?php
/**
 * Page d'administration des modules (partie initialisations)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_MODULE')) die;


# Modules locales
$okt->l10n->loadFile(__DIR__.'/../../../locales/'.$okt->user->language.'/bootstrap');


$bootstrap_module_name = 'My new module';
$bootstrap_module_name_fr = 'Mon nouveau module';
$bootstrap_module_description = 'A module that does nothing yet.';
$bootstrap_module_description_fr = 'Un module qui ne fait rien pour le moment.';
//$bootstrap_module_author = html::escapeHTML($okt->user->usedname);
$bootstrap_module_author = 'okatea.org';
$bootstrap_module_version = '0.1';
$bootstrap_module_licence = 'none';

$bootstrap_module_l10n_1_en = 'Items';
$bootstrap_module_l10n_1_fr = 'Éléments';
$bootstrap_module_l10n_2_en = 'Item';
$bootstrap_module_l10n_2_fr = 'Élément';
$bootstrap_module_l10n_3_en = 'items';
$bootstrap_module_l10n_3_fr = 'éléments';
$bootstrap_module_l10n_4_en = 'item';
$bootstrap_module_l10n_4_fr = 'élément';
$bootstrap_module_l10n_5_en = 'an item';
$bootstrap_module_l10n_5_fr = 'un élément';
$bootstrap_module_l10n_6_en = 'no item';
$bootstrap_module_l10n_6_fr = 'aucun élément';
$bootstrap_module_l10n_7_en = 'the item';
$bootstrap_module_l10n_7_fr = 'l’élément';
$bootstrap_module_l10n_8_en = 'The item';
$bootstrap_module_l10n_8_fr = 'L’élément';
$bootstrap_module_l10n_9_en = 'of the item';
$bootstrap_module_l10n_9_fr = 'de l’élément';
$bootstrap_module_l10n_10_en = 'this item';
$bootstrap_module_l10n_10_fr = 'cet élément';

$bootstrap_module_l10n_fem = false;


