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
		'env' => 'prod',

		'cookie_auth_name' 	=> 'otk_auth',
		'cookie_auth_from' 	=> 'otk_auth_from',
		'cookie_language' 	=> 'otk_language',

		'csrf_token_name' 	=> 'okt_csrf_token'
	);
}