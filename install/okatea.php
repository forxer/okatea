<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Install\Okatea;

# First, we check the PHP version required
$sPhpVersionRequired = require __DIR__.'/../Okatea/php_version_required.php';
if (!version_compare(PHP_VERSION, $sPhpVersionRequired, '>=')) {
	die(sprintf('PHP version is %s. Version %s or higher is required.', PHP_VERSION, $sPhpVersionRequired));
}

# Lunch composer autoload
$oktAutoloader = require __DIR__.'/../vendor/autoload.php';

# Let the music play
$okt = new Okatea($oktAutoloader, require __DIR__.'/../oktOptions.php');

$okt->run();

# -- CORE TRIGGER : installFinal
$okt->triggers->callTrigger('installFinal');
