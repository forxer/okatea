<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Initialisation d'Okatea.
 * 
 * Ce fichier est à utiliser dans un site ou une application externe. 
 * Il permet de lancer l'initialisation d'Okatea 
 *
 * @addtogroup Okatea
 */


/*// pour afficher les erreurs, ajoutez un / au début de cette ligne
error_reporting(-1);
ini_set('display_errors', 'On');
define('OKT_FORCE_DEBUG',true);
//*/


# initialisation Okatea
require_once __DIR__.'/oktInc/public/prepend.php';
