<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Le front controller de la partie administration.
 *
 */

use Tao\Admin\Okatea;

# Enable/disable debug mode
define('OKT_DEBUG', true);

# Environment ? 'dev' or 'prod'
if (!defined('OKT_ENVIRONMENT'))
{
	if (isset($_SERVER['SERVER_NAME']) && ($_SERVER['SERVER_NAME'] === '127.0.0.1' || $_SERVER['SERVER_NAME'] === 'localhost')) {
		define('OKT_ENVIRONMENT', 'dev');
	}
	else {
		define('OKT_ENVIRONMENT', 'prod');
	}
}

$oktAppPath = realpath(__DIR__.'/../');

# Lunch composer autoload
$oktAutoloader = require $oktAppPath.'/vendor/autoload.php';

# Let the music play
$okt = new Okatea($oktAutoloader, $oktAppPath, OKT_DEBUG, OKT_ENVIRONMENT);

$okt->run();

# -- CORE TRIGGER : adminFinal
$okt->triggers->callTrigger('adminFinal', $okt);

