<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Page d'administration des modules
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;


/* Initialisations
----------------------------------------------------------*/

require __DIR__.'/modules/init.php';


/* Traitements
----------------------------------------------------------*/

require __DIR__.'/modules/actions.php';


/* Affichage
----------------------------------------------------------*/

require __DIR__.'/modules/display.php';

