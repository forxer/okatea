<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Page d'administration des modules.
 *
 */

require __DIR__.'/../oktInc/admin/prepend.php';

$m = $okt->request->request->get('m', $okt->request->query->get('m'));

# module exists ?
if ($m === null || !$okt->modules->moduleExists($m) || !file_exists($okt->modules->getModuleObject($m)->root().'/admin.php'))
{
	# FIXME need 404 redirect
	http::redirect('index.php');
}

# get module admin file
require $okt->modules->getModuleObject($m)->root().'/admin.php';
