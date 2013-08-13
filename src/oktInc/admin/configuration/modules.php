<?php
/**
 * Page d'administration des modules
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

require dirname(__FILE__).'/modules/init.php';


/* Traitements
----------------------------------------------------------*/

require dirname(__FILE__).'/modules/actions.php';


/* Affichage
----------------------------------------------------------*/

require dirname(__FILE__).'/modules/display.php';

