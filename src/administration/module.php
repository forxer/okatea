<?php
/**
 * Page d'administration des modules
 *
 * @addtogroup Okatea
 *
 */

require dirname(__FILE__).'/../oktInc/admin/prepend.php';

$m = !empty($_REQUEST['m']) ? $_REQUEST['m'] : null;

# module exists ?
if ($m === null || !$okt->modules->moduleExists($m) || !file_exists($okt->modules->getModuleObject($m)->root().'/admin.php'))
{
	# FIXME need 404 redirect
	$okt->redirect('index.php');
}

# get module admin file
require $okt->modules->getModuleObject($m)->root().'/admin.php';
