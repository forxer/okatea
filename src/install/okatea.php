<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tao\Install\Application;

$oktAppPath = realpath(__DIR__.'/../');

# Lunch composer autoload
$oktAutoloader = require $oktAppPath.'/vendor/autoload.php';

$okt = new Application($oktAutoloader, $oktAppPath, require $oktAppPath.'/oktOptions.php');