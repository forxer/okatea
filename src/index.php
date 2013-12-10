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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

# Initialisation de la mécanique Okatea
require_once __DIR__.'/oktInc/public/prepend.php';

# Si on est en mode maintenance, il faut être superadmin
if ($okt->config->public_maintenance_mode && !$okt->user->is_superadmin) {
	$okt->page->serve503();
}

# -- CORE TRIGGER : publicBeforeMatchRequest
$okt->triggers->callTrigger('publicBeforeMatchRequest', $okt);

# Résolution de la route à utiliser
try {
	$okt->request->attributes->add(
		$okt->router->matchRequest($okt->request)
	);
}
catch (ResourceNotFoundException $e) {
	$okt->page->serve404();
}
catch (Exception $e) {
	$okt->response->headers->set('Content-Type', 'text/plain');
	$okt->response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
	$okt->response->setContent($e->message);
}

# -- CORE TRIGGER : publicAfterRouteFinded
$okt->triggers->callTrigger('publicAfterRouteFinded', $okt);


# Prepend language code
/*
if (!$okt->languages->unique && $matchRequest)
{
	http::head(301);
	http::redirect($okt->config->app_path.$okt->user->language.'/'.$okt->router->getPath());
}
*/

# -- CORE TRIGGER : publicBeforeCallController
$okt->triggers->callTrigger('publicBeforeCallController', $okt);

if ($okt->router->callController() === false)
{
	$okt->response->headers->set('Content-Type', 'text/plain');
	$okt->response->setStatusCode(Response::HTTP_NOT_IMPLEMENTED);
	$okt->response->setContent('Unable to load controller.');
}

# -- CORE TRIGGER : publicBeforePrepareResponse
$okt->triggers->callTrigger('publicBeforePrepareResponse', $okt);

$okt->response->prepare($okt->request);

# -- CORE TRIGGER : publicBeforeSendResponse
$okt->triggers->callTrigger('publicBeforeSendResponse', $okt);

$okt->response->send();

# -- CORE TRIGGER : publicFinal
$okt->triggers->callTrigger('publicFinal', $okt);
