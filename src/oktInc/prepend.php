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

# Définition du thème à utiliser
$sOktTheme = $okt->config->theme;


if (!empty($okt->config->theme_mobile) || !empty($okt->config->theme_tablet))
{
	if (isset($_REQUEST['disable_browser_check'])) {
		setcookie('okt_disable_browser_check', (boolean)$_REQUEST['disable_browser_check'], 0, $okt->config->app_path, '', isset($_SERVER['HTTPS']));
	}

	if (empty($_COOKIE['okt_disable_browser_check']))
	{
		$oMobileDetect = new Mobile_Detect();

		if ($oMobileDetect->isMobile() && !$oMobileDetect->isTablet() && !empty($okt->config->theme_mobile))
		{
			$sOktTheme = $okt->config->theme_mobile;
			setcookie('okt_use_mobile_theme', true, 0, $okt->config->app_path , '', isset($_SERVER['HTTPS']));
			setcookie('okt_use_tablet_theme', false, 0, $okt->config->app_path , '', isset($_SERVER['HTTPS']));
		}
		elseif ($oMobileDetect->isTablet() && !empty($okt->config->theme_tablet))
		{
			$sOktTheme = $okt->config->theme_tablet;
			setcookie('okt_use_mobile_theme', false, 0, $okt->config->app_path , '', isset($_SERVER['HTTPS']));
			setcookie('okt_use_tablet_theme', true, 0, $okt->config->app_path , '', isset($_SERVER['HTTPS']));
		}
		else
		{
			setcookie('okt_use_mobile_theme', false, 0, $okt->config->app_path , '', isset($_SERVER['HTTPS']));
			setcookie('okt_use_tablet_theme', false, 0, $okt->config->app_path , '', isset($_SERVER['HTTPS']));
		}

		setcookie('okt_disable_browser_check', true, 0, $okt->config->app_path , '', isset($_SERVER['HTTPS']));

		unset($oMobileDetect);
	}
	elseif (!empty($_COOKIE['okt_use_mobile_theme']) && !empty($okt->config->theme_mobile)) {
		$sOktTheme = $okt->config->theme_mobile;
	}
	elseif (!empty($_COOKIE['okt_use_tablet_theme']) && !empty($okt->config->theme_tablet)) {
		$sOktTheme = $okt->config->theme_tablet;
	}
}

# URL du thème
define('OKT_THEME', $okt->config->app_path.OKT_THEMES_DIR.'/'.$sOktTheme);

# Chemin du thème
define('OKT_THEME_PATH', OKT_THEMES_PATH.'/'.$sOktTheme);

# Store upload_max_filesize in bytes
$u_max_size = files::str2bytes(ini_get('upload_max_filesize'));
$p_max_size = files::str2bytes(ini_get('post_max_size'));
if ($p_max_size < $u_max_size) {
	$u_max_size = $p_max_size;
}
define('OKT_MAX_UPLOAD_SIZE',$u_max_size);
unset($u_max_size,$p_max_size);

# initialisation du moteur de templates

	# enregistrement des répertoires de templates
	$okt->setTplDirectory(OKT_THEME_PATH.'/templates/%name%.php');
	$okt->setTplDirectory(OKT_THEMES_PATH.'/default/templates/%name%.php');

	# initialisation
	$okt->initTplEngine();

	# assignation par défaut
	$okt->tpl->addGlobal('okt', $okt);


# Changement de langue utilisateur
if (!empty($_REQUEST['switch_lang']))
{
	$okt->user->setUserLang($_REQUEST['switch_lang']);

	http::redirect(util::removeAttrFromUrl('switch_lang', $okt->config->self_uri));
}

# Suppression des fichiers cache
if (!empty($_REQUEST['empty_cache']))
{
	util::deleteOktCacheFiles();
	util::deleteOktPublicCacheFiles();

	http::redirect(util::removeAttrFromUrl('empty_cache', $okt->config->self_uri));
}
