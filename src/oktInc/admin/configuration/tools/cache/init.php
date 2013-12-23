<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Outil gestion du cache (partie initialisation)
 *
 * @addtogroup Okatea
 *
 */

use Tao\Misc\Utilities as util;

# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;


# liste des fichiers cache
$aCacheFiles = util::getOktCacheFiles();

# liste des fichiers cache public
$aPublicCacheFiles = util::getOktPublicCacheFiles();