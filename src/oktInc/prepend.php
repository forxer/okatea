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

use Tao\Core\Application;
use Tao\Misc\Utilities as util;

# Enable/disable debug mode
define('OKT_DEBUG', true);

# Add system constants
//require_once __DIR__.'/constants.php';

# Lunch composer autoload
$oktAutoloader = require OKT_VENDOR_PATH.'/autoload.php';

# Let the music play (initialisation du coeur de l'application)
$okt = new Application($oktAutoloader);


# Switch use language
if (!empty($_REQUEST['switch_lang']))
{
	$okt->user->setUserLang($_REQUEST['switch_lang']);

	http::redirect(util::removeAttrFromUrl('switch_lang', $okt->request->getUri()));
}

# Clear cache
if (!empty($_REQUEST['empty_cache']) && $okt->user->is_superadmin)
{
	util::deleteOktCacheFiles();
	util::deleteOktPublicCacheFiles();

	http::redirect(util::removeAttrFromUrl('empty_cache', $okt->request->getUri()));
}
