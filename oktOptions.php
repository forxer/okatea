<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$aOptions = [];

	# The name of the software, if you want to change it,
	# for example for companies who want to develop their own version.
	$aOptions['software_name'] 	= 'Okatea';

	# The URL of the software website, if you want to change it,
	# for example for companies who want to develop their own version
	$aOptions['software_url'] 	= 'http://okatea.org/';

	# Enable or disable the debug mode
	$aOptions['debug'] 	= false;

	# Set the environement, should be 'prod' or 'dev'
	$aOptions['env'] 	= 'prod';

	# Full path to the application directory
	$aOptions['app_path'] 		= __DIR__;

	# URL path to the application directory
	$aOptions['app_url'] 		= '/';

	# Full path to the Okatea directory
	$aOptions['okt_path'] 		= $aOptions['app_path'] . '/Okatea';

		# Full path to the cache directory
		$aOptions['cache_path'] 	= $aOptions['app_path'] . '/Okatea/Cache';

		# Full path to the config directory
		$aOptions['config_path'] 	= $aOptions['app_path'] . '/Okatea/Config';

		# Full path to the locales directory
		$aOptions['locales_path'] 	=  $aOptions['app_path'] . '/Okatea/Locales';

		# Full path to the logs directory
		$aOptions['logs_path'] 		= $aOptions['app_path'] . '/Okatea/Logs';

		# Full path to the modules directory
		$aOptions['modules_path'] 	= $aOptions['app_path'] . '/Okatea/Modules';

		# Full path to the themes directory
		$aOptions['themes_path'] 	=  $aOptions['app_path'] . '/Okatea/Themes';

	# Full path to the public directory
	$aOptions['public_path'] 	= $aOptions['app_path'] . '/oktPublic';

	# URL path to the public directory
	$aOptions['public_url'] 		= $aOptions['app_url'] . '/oktPublic';

		# Full path to the upload directory
		$aOptions['upload_path'] 	= $aOptions['public_path'] . '/upload';

		# URL path to the upload directory
		$aOptions['upload_url'] 	= $aOptions['public_url'] . '/upload';

	# Full path to the digests file
	$aOptions['digests_path'] 	= $aOptions['app_path'] . '/digests';

	# Define application cookies names
	$aOptions['cookie_auth_name'] 	= 'otk_auth';
	$aOptions['cookie_auth_from'] 	= 'otk_auth_from';
	$aOptions['cookie_language'] 	= 'otk_language';

	# The CSRF token name
	$aOptions['csrf_token_name'] 	= 'okt_csrf_token';


return $aOptions;

