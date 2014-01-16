<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (file_exists(__DIR__.'/oktOptions.custom.php')) {
	return __DIR__.'/oktOptions.custom.php';
}
else
{
	return array(
		'debug' => false,
		'env' => 'prod'
	);
}