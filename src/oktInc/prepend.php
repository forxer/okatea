<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @file Fichier commun
 *
 * @addtogroup Okatea
 *
 */

use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Tao\Core\Application;
use Tao\Core\Authentification;
use Tao\Core\Languages;
use Tao\Core\Localisation;
use Tao\Misc\Utilities as util;

/*
 * Activation/désactivation du mode debug
 */
define('OKT_DEBUG', true);

/*
 * Enregistrement du moment de début de script, sera utilisé
 * pour calculer le temps de génération des pages
 */
define('OKT_START_TIME', !empty($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] : microtime(true));

# Inclusion des constantes systèmes
require_once __DIR__.'/constants.php';

# Use composer autoload
$oktAutoloader = require OKT_VENDOR_PATH.'/autoload.php';

# Inclusion des informations de connexion à la BDD
if (file_exists(OKT_CONFIG_PATH.'/connexion.php')) {
	require_once OKT_CONFIG_PATH.'/connexion.php';
}
else {
	oktErrors::fatalScreen('Fatal error: unable to find database connexion file !');
}

# Start debug mode
if (OKT_DEBUG)
{
	Debug::enable();
	ErrorHandler::register();
	ExceptionHandler::register();
}

# Initialisation de la librairie MB
mb_internal_encoding('UTF-8');

# Fuseau horraire par défaut (écrasé par la suite par les réglages utilisateurs)
date_default_timezone_set('Europe/Paris');


# Let the music play (initialisation du coeur de l'application)
$okt = new Application($oktAutoloader);


# Changement de langue utilisateur
if (!empty($_REQUEST['switch_lang']))
{
	$okt->user->setUserLang($_REQUEST['switch_lang']);

	http::redirect(util::removeAttrFromUrl('switch_lang', $okt->config->self_uri));
}

# Suppression des fichiers cache
if (!empty($_REQUEST['empty_cache']) && $okt->user->is_superadmin)
{
	util::deleteOktCacheFiles();
	util::deleteOktPublicCacheFiles();

	http::redirect(util::removeAttrFromUrl('empty_cache', $okt->config->self_uri));
}
