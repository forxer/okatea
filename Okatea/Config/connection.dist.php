<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Configuration data connection to the database.
 */
if (! isset($env))
{
	$env = $this['env'];
}

# Development environment
if ($env == 'dev')
{
	# database host
	$sDbHost = '%%DB_DEV_HOST%%';

	# database name
	$sDbName = '%%DB_DEV_BASE%%';

	# database user name
	$sDbUser = '%%DB_DEV_USER%%';

	# database password
	$sDbPassword = '%%DB_DEV_PASS%%';

	# database tables prefix
	$sDbPrefix = '%%DB_DEV_PREFIX%%';

	# database driver
	$sDbDriver = 'pdo_mysql';
}

# Production environment
elseif ($env == 'prod')
{
	# database host
	$sDbHost = '%%DB_PROD_HOST%%';

	# database name
	$sDbName = '%%DB_PROD_BASE%%';

	# database user name
	$sDbUser = '%%DB_PROD_USER%%';

	# database password
	$sDbPassword = '%%DB_PROD_PASS%%';

	# database tables prefix
	$sDbPrefix = '%%DB_PROD_PREFIX%%';

	# database driver
	$sDbDriver = 'pdo_mysql';
}
else
{
	throw new \Exception('The system does not find an acceptable environment for connecting to database. Accepted environments are "dev" or "prod".');
}
