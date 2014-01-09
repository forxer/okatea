<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Okatea Front Controller.
 */

use Tao\Website\Okatea;

# Lunch composer autoload
$oktAutoloader = require __DIR__.'/vendor/autoload.php';

# Let the music play
$okt = new Okatea($oktAutoloader, __DIR__);

$okt->run();

# -- CORE TRIGGER : publicFinal
$okt->triggers->callTrigger('publicFinal', $okt);
