<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @file
 * @addtogroup Okatea
 * @brief L'autoload Okatea.
 *
 * Rudimentaire mais efficace : un tableau ayant pour index les noms des classes
 * et pour valeur le chemin du fichier à inclure.
 *
 *
 */

$oktAutoloadPaths = array();


# internal autoload
function okt_autoload($name)
{
	global $oktAutoloadPaths;

	if (isset($oktAutoloadPaths[$name])) {
		require $oktAutoloadPaths[$name];
	}
}

spl_autoload_register('okt_autoload');

