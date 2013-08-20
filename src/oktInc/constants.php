<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Constantes système
 *
 * @addtogroup Okatea
 *
 */


/**
 * Chemin du dossier racine
 */
define('OKT_ROOT_PATH', realpath(__DIR__.'/../'));

/**
 * Chemin du dossier include
 */
define('OKT_INC_DIR','oktInc');
define('OKT_INC_PATH', OKT_ROOT_PATH.DIRECTORY_SEPARATOR.OKT_INC_DIR);

/**
 * Nom et chemin du dossier upload
 */
define('OKT_UPLOAD_DIR','oktUpload');
define('OKT_UPLOAD_PATH', OKT_ROOT_PATH.DIRECTORY_SEPARATOR.OKT_UPLOAD_DIR);

/**
 * Nom et chemin du dossier de fichiers publics communs
 */
define('OKT_COMMON_DIR','oktCommon');
define('OKT_COMMON_PATH', OKT_ROOT_PATH.DIRECTORY_SEPARATOR.OKT_COMMON_DIR);

/**
 * Nom et chemin du dossier modules
 */
define('OKT_MODULES_DIR', 'oktModules');
define('OKT_MODULES_PATH', OKT_ROOT_PATH.DIRECTORY_SEPARATOR.OKT_MODULES_DIR);

/**
 * Nom et chemin du dossier public
 */
define('OKT_PUBLIC_DIR', 'oktPublic');
define('OKT_PUBLIC_PATH', OKT_ROOT_PATH.DIRECTORY_SEPARATOR.OKT_PUBLIC_DIR);

/**
 * Nom et chemin du dossier cache
 */
define('OKT_CACHE_DIR', 'cache');
define('OKT_CACHE_PATH', OKT_INC_PATH.DIRECTORY_SEPARATOR.OKT_CACHE_DIR);

/**
 * Nom et chemin du fichier cache global
 */
define('OKT_GLOBAL_CACHE_FILE', OKT_CACHE_PATH.DIRECTORY_SEPARATOR.'static.php');

/**
 * Chemin du dossier de configuration
 */
define('OKT_CONFIG_DIR', 'conf');
define('OKT_CONFIG_PATH', OKT_INC_PATH.DIRECTORY_SEPARATOR.OKT_CONFIG_DIR);


/**
 * Chemin du dossier de logs
 */
define('OKT_LOG_DIR', 'logs');
define('OKT_LOG_PATH', OKT_INC_PATH.DIRECTORY_SEPARATOR.OKT_LOG_DIR);

/**
 * Chemin du dossier des thèmes
 */
define('OKT_THEMES_DIR', 'oktThemes');
define('OKT_THEMES_PATH', OKT_ROOT_PATH.DIRECTORY_SEPARATOR.OKT_THEMES_DIR);

/**
 * Chemin du dossier classes
 */
define('OKT_CLASSES_PATH', OKT_INC_PATH.DIRECTORY_SEPARATOR.'classes');

/**
 * Chemin du dossier locales
 */
define('OKT_LOCALES_PATH', OKT_INC_PATH.DIRECTORY_SEPARATOR.'locales');

/**
 * Chemin du dossier vendor
 */
define('OKT_VENDOR_PATH', OKT_INC_PATH.DIRECTORY_SEPARATOR.'vendor');

/**
 * Nom du cookie d'identification
 */
if (!defined('OKT_COOKIE_AUTH_NAME')) {
	define('OKT_COOKIE_AUTH_NAME', 'otk_auth');
}

/**
 * Nom du cookie de retour après identification
 */
if (!defined('OKT_COOKIE_AUTH_FROM')) {
	define('OKT_COOKIE_AUTH_FROM', 'otk_auth_from');
}

/**
 * Nom du cookie de langue
 */
if (!defined('OKT_COOKIE_LANGUAGE')) {
	define('OKT_COOKIE_LANGUAGE', 'otk_language');
}

/**
 * L'environnement
 */
if (!defined('OKT_ENVIRONMENT'))
{
	define('OKT_ENVIRONMENT',
		(isset($_SERVER['SERVER_NAME']) && ($_SERVER['SERVER_NAME'] === '127.0.0.1' || $_SERVER['SERVER_NAME'] === 'localhost'))
		? 'dev'
		: 'prod'
	);
}

/**
 * Présence de Xdebug ?
 */
if (!defined('OKT_XDEBUG')) {
	define('OKT_XDEBUG', function_exists('xdebug_is_enabled'));
}

/**
 * Raccourci du nom du fichier en cours
 */
if (!defined('OKT_FILENAME')) {
	define('OKT_FILENAME', basename($_SERVER['PHP_SELF']));
}

/**
 * Raccourci du nom du dossier en cours
 */
if (!defined('OKT_DIRNAME')) {
	define('OKT_DIRNAME', dirname($_SERVER['PHP_SELF']));
	//define('OKT_DIRNAME', str_replace(DIRECTORY_SEPARATOR,'/',dirname($_SERVER['PHP_SELF'])));
}

/**
 * Le nom du dossier admin
 */
if (!defined('OKT_ADMIN_DIR')) {
	define('OKT_ADMIN_DIR', 'administration');
}

/**
 * Le fichier en-tête de l'administration
 */
if (!defined('OKT_ADMIN_HEADER_FILE')) {
	define('OKT_ADMIN_HEADER_FILE', OKT_INC_PATH.DIRECTORY_SEPARATOR.'admin/header.php');
}

/**
 * Le fichier pied-de-page de l'administration
 */
if (!defined('OKT_ADMIN_FOOTER_FILE')) {
	define('OKT_ADMIN_FOOTER_FILE', OKT_INC_PATH.DIRECTORY_SEPARATOR.'admin/footer.php');
}

/**
 * Le fichier en-tête simple de l'administration
 */
if (!defined('OKT_ADMIN_HEADER_SIMPLE_FILE')) {
	define('OKT_ADMIN_HEADER_SIMPLE_FILE', OKT_INC_PATH.DIRECTORY_SEPARATOR.'admin/header_simple.php');
}

/**
 * Le fichier pied-de-page simple de l'administration
 */
if (!defined('OKT_ADMIN_FOOTER_SIMPLE_FILE')) {
	define('OKT_ADMIN_FOOTER_SIMPLE_FILE', OKT_INC_PATH.DIRECTORY_SEPARATOR.'admin/footer_simple.php');
}

/**
 * La page de connexion à l'administration
 */
if (!defined('OKT_ADMIN_LOGIN_PAGE')) {
	define('OKT_ADMIN_LOGIN_PAGE', 'connexion.php');
}
