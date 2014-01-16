<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$aOktDefaultOptions = array(

	# enable or disable debug mode
	'debug' => false,

	# set the environement, should be 'prod' or 'dev'
	'env' => 'prod',

	# define several directories paths
	'root_dir' 			=> __DIR__,
	'inc_dir' 			=> __DIR__.'/oktInc',
	'cache_dir' 		=> __DIR__.'/oktInc/cache',
	'config_dir' 		=> __DIR__.'/oktInc/config',
	'locales_dir' 		=> __DIR__.'/oktInc/locales',
	'logs_dir' 			=> __DIR__.'/oktInc/logs',
	'modules_dir' 		=> __DIR__.'/oktInc/Modules',
	'public_dir' 		=> __DIR__.'/oktPublic',
	'upload_dir' 		=> __DIR__.'/oktPublic/upload',
	'themes_dir' 		=> __DIR__.'/oktThemes',

	# cookies names
	'cookie_auth_name' 	=> 'otk_auth',
	'cookie_auth_from' 	=> 'otk_auth_from',
	'cookie_language' 	=> 'otk_language',

	# the CSRF token name
	'csrf_token_name' 	=> 'okt_csrf_token'
);

# import customs options
if (file_exists(__DIR__.'/oktOptions.custom.php'))
{
	$aOktCustomOptions = require __DIR__.'/oktOptions.custom.php';
	return $aOktCustomOptions + $aOktDefaultOptions;
}

return $aOktDefaultOptions;

