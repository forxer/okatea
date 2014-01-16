<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Install\Okatea;

# Lunch composer autoload
$oktAutoloader = require __DIR__.'/../vendor/autoload.php';

$okt = new Okatea($oktAutoloader, require __DIR__.'/../oktOptions.php');

$okt->run();

# -- CORE TRIGGER : installFinal
$okt->triggers->callTrigger('installFinal');
