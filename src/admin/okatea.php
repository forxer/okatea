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

$oktAppPath = realpath(__DIR__.'/../');

# Lunch composer autoload
$oktAutoloader = require $oktAppPath.'/vendor/autoload.php';

# Let the music play
$okt = new Okatea($oktAutoloader, $oktAppPath);

$okt->run();

# -- CORE TRIGGER : adminFinal
$okt->triggers->callTrigger('adminFinal', $okt);

