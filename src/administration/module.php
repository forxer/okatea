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

$m = $okt->modules->getActiveModule();

$moduleInstance = $okt->modules->getModuleObject($m);

# module exists ?
if (!$okt->modules->moduleExists($m) || !file_exists($moduleInstance->root().'/admin.php'))
{
	# FIXME need 404 redirect
	http::redirect('index.php');
}

define('ON_MODULE', true);

# get module admin file
require $moduleInstance->root().'/admin.php';
