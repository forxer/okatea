<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$aOktOptions = [];

	# The name of the software, if you want to change it,
	# for example for companies who want to develop their own version.
	$aOktOptions['software_name'] 	= 'Okatea';

	# The URL of the software website, if you want to change it,
	# for example for companies who want to develop their own version
	$aOktOptions['software_url'] 	= 'http://okatea.org/';

	# Enable or disable the debug mode
	$aOktOptions['debug'] 	= false;

	# Set the environement, should be 'prod' or 'dev'
	$aOktOptions['env'] 	= 'prod';

	# Full path to the application directory
	$aOktOptions['app_path'] 		= __DIR__;

	# Full path to the Okatea directory
	$aOktOptions['okt_path'] 		= $aOktOptions['app_path'] . '/Okatea';

		# Full path to the cache directory
		$aOktOptions['cache_path'] 	= $aOktOptions['app_path'] . '/Okatea/Cache';

		# Full path to the config directory
		$aOktOptions['config_path'] 	= $aOktOptions['app_path'] . '/Okatea/Config';

		# Full path to the locales directory
		$aOktOptions['locales_path'] 	=  $aOktOptions['app_path'] . '/Okatea/Locales';

		# Full path to the logs directory
		$aOktOptions['logs_path'] 		= $aOktOptions['app_path'] . '/Okatea/Logs';

		# Full path to the modules directory
		$aOktOptions['modules_path'] 	= $aOktOptions['app_path'] . '/Okatea/Modules';

		# Full path to the themes directory
		$aOktOptions['themes_path'] 	=  $aOktOptions['app_path'] . '/Okatea/Themes';

	# Full path to the public directory
	$aOktOptions['public_path'] 	= $aOktOptions['app_path'] . '/oktPublic';

		# Full path to the upload directory
		$aOktOptions['upload_path'] 	= $aOktOptions['public_path'] . '/upload';

	# Full path to the digests file
	$aOktOptions['digests_path'] 	= $aOktOptions['app_path'] . '/digests';

	# Define application cookies names
	$aOktOptions['cookie_auth_name'] 	= 'otk_auth';
	$aOktOptions['cookie_auth_from'] 	= 'otk_auth_from';
	$aOktOptions['cookie_language'] 	= 'otk_language';

	# The CSRF token name
	$aOktOptions['csrf_token_name'] 	= 'okt_csrf_token';



# Import customs options
$sCustomOptionsFile = __DIR__ . '/oktOptions.custom.php';
if (file_exists($sCustomOptionsFile))
{
	$aOktCustomOptions = require $sCustomOptionsFile;
	return $aOktCustomOptions + $aOktOptions;
}

return $aOktOptions;
