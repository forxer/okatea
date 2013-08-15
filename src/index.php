<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Okatea Front Controller ; one file to route them all
 *
 * @addtogroup Okatea
 */


/*// pour afficher les erreurs, ajoutez un / au début de cette ligne
error_reporting(-1);
ini_set('display_errors', 'On');
define('OKT_FORCE_DEBUG',true);
//*/


# Inclusion de l'éventuel prepend customisé
if (file_exists(dirname(__FILE__).'/oktPrepend.php')) {
	require_once dirname(__FILE__).'/oktPrepend.php';
}

# Initialisation de la mécanique Okatea
require_once dirname(__FILE__).'/oktInc/public/prepend.php';

# Routeur activé ?
if (!$okt->config->internal_router) {
	$okt->page->serve404();
}

# Si on est en mode maintenance, il faut être superadmin
elseif ($okt->config->public_maintenance_mode && !$okt->user->is_superadmin) {
	$okt->page->serve503();
}

# Résolution de la route à utiliser
if ($okt->router->findRoute() === false) {
	$okt->page->serve404();
}

# -- CORE TRIGGER : publicAfterRouteFinded
$okt->triggers->callTrigger('publicAfterRouteFinded', $okt);

# Prepend language code
if (!$okt->languages->unique && !$okt->router->getLanguage() && !is_null($okt->router->getPath()))
{
	http::head(301);
	http::redirect($okt->config->app_path.$okt->user->language.'/'.$okt->router->getPath());
}

# Start output buffering
ob_start();

# -- CORE TRIGGER : publicBeforeController
$okt->triggers->callTrigger('publicBeforeController', $okt);

# Appel le gestionnaire de la route trouvée
if ($okt->router->callRouteHanlder() === false) {
	$okt->page->serve404();
}

# Get buffer contents
$okt->page->content = ob_get_clean();

# -- CORE TRIGGER : publicBeforeSendContent
$okt->triggers->callTrigger('publicBeforeSendContent', $okt);

# the end
echo $okt->page->content;
