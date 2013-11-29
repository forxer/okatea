<?php
/**
 * Fichier pour l'installation du système.
 *
 * @addtogroup Okatea
 * @subpackage 		Install interface
 *
 */

define('OKT_SUDO_USERNAME', 'sudo');
define('OKT_SUDO_EMAIL', 'sudo@localhost');


@ini_set('error_reporting', E_ALL);
@ini_set('display_errors', 'On');


define('OKT_INSTAL_DIR',__DIR__);
define('OKT_INSTAL_COMMON_URL','./../oktPublic');


define('OKT_INSTAL_URL','./');
define('OKT_INSTAL_PROCESS',true);


# locales disponibles
$aAvailablesLocales = array('fr','en');
$sDefaultLanguage = 'fr';


# Inclusion des constantes
require_once __DIR__.'/../oktInc/constants.php';


# Use composer autoload
$oktAutoloader = require OKT_VENDOR_PATH.'/autoload.php';

$oktAutoloader->addClassMap(array(
	'oktStepper' => __DIR__.'/inc/class.stepper.php',
	'adminMessagesErrors' => OKT_INC_PATH.'/admin/libs/lib.admin.messages.errors.php',
	'adminMessagesSuccess' => OKT_INC_PATH.'/admin/libs/lib.admin.messages.success.php',
	'adminMessagesWarnings' => OKT_INC_PATH.'/admin/libs/lib.admin.messages.warnings.php',
	'adminPage' => OKT_INC_PATH.'/admin/libs/lib.admin.page.php',
	'adminPager' => OKT_INC_PATH.'/admin/libs/lib.admin.pager.php',
	'adminFilters' => OKT_INC_PATH.'/admin/libs/lib.admin.filters.php'
));


# Initialisation de la librairie MB
mb_internal_encoding('UTF-8');


# Fuseau horraire par défaut (écrasé par la suite par les réglages utilisateurs)
date_default_timezone_set('Europe/Paris');


/*
 * Destruction des variables globales créees si
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


# start sessions... - ah bon ? - hé oui ! - ah ah !
if (!session_id()) {
	session_start();
}


# Install or update ?
if (!isset($_SESSION['okt_install_process_type']))
{
	$_SESSION['okt_install_process_type'] = 'install';

	if (file_exists(OKT_CONFIG_PATH.'/connexion.php')) {
		$_SESSION['okt_install_process_type'] = 'update';
	}
}

$sOldVersion = !empty($_REQUEST['old_version']) ? trim($_REQUEST['old_version']) : null;


# Initialisation localisation
if (!isset($_SESSION['okt_install_language']))
{
	$sAcceptLanguage = http::getAcceptLanguage();

	if (in_array($sAcceptLanguage, $aAvailablesLocales) && $sAcceptLanguage != $sDefaultLanguage) {
		$_SESSION['okt_install_language'] = $sAcceptLanguage;
	}
	else {
		$_SESSION['okt_install_language'] = $sDefaultLanguage;
	}

	http::redirect('index.php');
}

if (isset($_REQUEST['switch_language']) && in_array($_REQUEST['switch_language'], $aAvailablesLocales))
{
	$_SESSION['okt_install_language'] = $_REQUEST['switch_language'];
	http::redirect('index.php');
}


# load locales
l10n::init();
l10n::set(OKT_LOCALES_PATH.'/'.$_SESSION['okt_install_language'].'/main');
l10n::set(OKT_INSTAL_DIR.'/inc/locales/'.$_SESSION['okt_install_language'].'/install');

# HTML page helper
require_once OKT_INC_PATH.'/admin/libs/lib.admin.page.php';
$oHtmlPage = new adminPage(null);

# CSS
$oHtmlPage->css->addFile(OKT_INSTAL_COMMON_URL.'/ui-themes/redmond/jquery-ui.css');
$oHtmlPage->css->addFile(OKT_INSTAL_COMMON_URL.'/css/init.css');
$oHtmlPage->css->addFile(OKT_INSTAL_COMMON_URL.'/css/admin.css');
$oHtmlPage->css->addFile(OKT_INSTAL_COMMON_URL.'/css/famfamfam.css');
$oHtmlPage->css->addCSS(file_get_contents(OKT_INSTAL_DIR.'/assets/install.css'));

# JS
$oHtmlPage->js->addFile(OKT_INSTAL_COMMON_URL.'/js/jquery/jquery.min.js');
$oHtmlPage->js->addFile(OKT_INSTAL_COMMON_URL.'/js/jquery/cookie/jquery.cookie.min.js');
$oHtmlPage->js->addFile(OKT_INSTAL_COMMON_URL.'/js/jquery/ui/jquery-ui.min.js');
$oHtmlPage->js->addFile(OKT_INSTAL_COMMON_URL.'/js/common_admin.js');
$oHtmlPage->js->addFile(OKT_INSTAL_COMMON_URL.'/js/jquery/blockUI/jquery.blockUI.min.js');

# load page from stepper
require_once __DIR__.'/inc/'.$_SESSION['okt_install_process_type'].'Stepper.php';

$sCurrentPageFilename = OKT_INSTAL_DIR.'/inc/pages/'.$stepper->getCurrentStep().'.php';

if (file_exists($sCurrentPageFilename)) {
	require $sCurrentPageFilename;
}
else {
	die('Aller Ginette !');
}

