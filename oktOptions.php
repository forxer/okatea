<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$aOktDefaultOptions = array(

	# the name of the software, if you want to change it,
	# for example for companies who want to develop their own version
	'software_name'		=> 'Okatea',

	# the URL of the software website, if you want to change it,
	# for example for companies who want to develop their own version
	'software_url'		=> 'http://okatea.org/',

	# enable or disable debug mode
	'debug' 			=> false,

	# set the environement, should be 'prod' or 'dev'
	'env' 				=> 'prod',

	# define several directories paths
	'root_dir' 			=> __DIR__,
	'okt_dir' 			=> __DIR__.'/Okatea',
	'cache_dir' 		=> __DIR__.'/Okatea/Cache',
	'config_dir' 		=> __DIR__.'/Okatea/Config',
	'locales_dir' 		=> __DIR__.'/Okatea/Locales',
	'logs_dir' 			=> __DIR__.'/Okatea/Logs',
	'modules_dir' 		=> __DIR__.'/Okatea/Modules',
	'themes_dir' 		=> __DIR__.'/Okatea/Themes',
	'public_dir' 		=> __DIR__.'/oktPublic',
	'upload_dir' 		=> __DIR__.'/oktPublic/upload',
	'digests' 			=> __DIR__.'/Okatea/digests',

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

