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


/*// pour afficher les erreurs, ajoutez un / au début de cette ligne
error_reporting(-1);
ini_set('display_errors', 'On');
//define('OKT_FORCE_DEBUG',true);
//*/


/*
 * Enregistrement du moment de début de script, sera utilisé
 * pour calculer le temps de génération des pages
 */
define('OKT_START_TIME', microtime(true));


# Inclusion des constantes systèmes
require_once __DIR__.'/constants.php';


# Inclusion de l'autoload
require_once OKT_INC_PATH.'/autoload.php';


# Inclusion des fonctions
require_once OKT_INC_PATH.'/functions.php';


# Inclusion des informations de connexion à la BDD
if (file_exists(OKT_CONFIG_PATH.'/connexion.php')) {
	require_once OKT_CONFIG_PATH.'/connexion.php';
}
else {
	oktErrors::fatalScreen('Fatal error: unable to find database connexion file !');
}


# Initialisation de la librairie MB
mb_internal_encoding('UTF-8');


# Fuseau horraire par défaut (écrasé par la suite par les réglages utilisateurs)
date_default_timezone_set('Europe/Paris');


# Shutdown
register_shutdown_function('oktShutdown');


/*
 * Destruction des variables globales créées si
 * register_globals est activé et inversion de
 * l'effet des magic_quotes
 */
util::trimRequest();
try {
	http::unsetGlobals();
}
catch (Exception $e)
{
	header('Content-Type: text/plain');
	echo $e->getMessage();
	exit;
}


# Let the music play (initialisation du coeur de l'application)
$okt = new oktCore();


# Chargement de la configuration du site
$okt->loadConfig();


# URL du dossier modules
define('OKT_MODULES_URL', $okt->config->app_path.OKT_MODULES_DIR);

# URL du dossier des fichiers publics
define('OKT_PUBLIC_URL', $okt->config->app_path.OKT_PUBLIC_DIR);

# URL du dossier upload depuis la racine
define('OKT_UPLOAD_URL', $okt->config->app_path.OKT_PUBLIC_DIR.'/'.OKT_UPLOAD_DIR);


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


# Mode debug ?
if ((!OKT_XDEBUG || !$okt->config->xdebug_enabled) && (defined('OKT_FORCE_DEBUG') || $okt->config->debug_enabled)) {
	define('OKT_DEBUG',true);
} else {
	define('OKT_DEBUG',false);
}


if (OKT_DEBUG)
{
	$okt->debug = new oktDebug();

	error_reporting(-1);
	ini_set('display_errors', 'On');

	/* exemple :
		$okt->debug->addMessage(array(
			'message'	=> 'Initialisation debug',
			'style'		=> 'info'
		));
	//*/

	set_error_handler(array($okt->debug,'errorHandler'), E_ALL | E_STRICT);
}


# Set session params
if (!session_id())
{
	ini_set('session.use_trans_sid', false);
	ini_set('session.use_only_cookies', true);
	session_set_cookie_params(0, $okt->config->app_path, '', isset($_SERVER['HTTPS']), true);
}


# Initialisation langues
$okt->languages = new oktLanguages($okt);
$okt->languages->load();


# Initialisation utilisateur courant
$okt->user = new oktAuth($okt, OKT_COOKIE_AUTH_NAME, OKT_COOKIE_AUTH_FROM, $okt->config->app_path, '', isset($_SERVER['HTTPS']));
$okt->user->authentication();
$okt->user->initLanguage(OKT_COOKIE_LANGUAGE);

	# Initialisation localisation
	l10n::init();
	l10n::set(OKT_LOCALES_PATH.'/'.$okt->user->language.'/main');
	l10n::set(OKT_LOCALES_PATH.'/'.$okt->user->language.'/date');

	# Défintion du fuseau horraire de l'utilisateur
	dt::setTZ($okt->user->timezone);


# Initialisation navigations
$okt->navigation = new oktNavigations($okt);


# Initialisation du gestionnaire de modules
$okt->modules = new oktModules($okt, OKT_MODULES_PATH, OKT_MODULES_URL);


# initialisation du moteur de templates

	# enregistrement des répertoires de templates
	$okt->setTplDirectory(OKT_THEME_PATH.'/templates/%name%.php');
	$okt->setTplDirectory(OKT_THEMES_PATH.'/default/templates/%name%.php');

	# initialisation
	$okt->initTplEngine();

	# assignation par défaut
	$okt->tpl->assign(array('okt'=>$okt));


# Changement de langue utilisateur
if (!empty($_REQUEST['switch_lang']))
{
	$okt->user->setUserLang($_REQUEST['switch_lang']);

	$okt->redirect(util::removeAttrFromUrl('switch_lang',$okt->config->self_uri));
}

# Suppression des fichiers cache
if (!empty($_REQUEST['empty_cache']))
{
	util::deleteOktCacheFiles();
	util::deleteOktPublicCacheFiles();

	$okt->redirect(util::removeAttrFromUrl('empty_cache',$okt->config->self_uri));
}
