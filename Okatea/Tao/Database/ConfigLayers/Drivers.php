<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Database\ConfigLayers;

class Drivers
{
	static $drivers = [
		'pdo_mysql' 			=> 'A MySQL driver that uses the pdo_mysql PDO extension.',
		'drizzle_pdo_mysql' 	=> 'A Drizzle driver that uses pdo_mysql PDO extension.',
		'mysqli' 				=> 'A MySQL driver that uses the mysqli extension.',
		'pdo_sqlite' 			=> 'An SQLite driver that uses the pdo_sqlite PDO extension.',
		'pdo_pgsql' 			=> 'A PostgreSQL driver that uses the pdo_pgsql PDO extension.',
		'pdo_oci' 				=> 'An Oracle driver that uses the pdo_oci PDO extension. Note that this driver caused problems in Doctrine DBAL tests. Prefer the oci8 driver if possible.',
		'pdo_sqlsrv' 			=> 'A Microsoft SQL Server driver that uses pdo_sqlsrv PDO. Note that this driver caused problems in Doctrine DBAL tests. Prefer the sqlsrv driver if possible.',
		'sqlsrv' 				=> 'A Microsoft SQL Server driver that uses the sqlsrv PHP extension.',
		'oci8' 					=> 'An Oracle driver that uses the oci8 PHP extension.',
		'sqlanywhere' 			=> 'A SAP Sybase SQL Anywhere driver that uses the sqlanywhere PHP extension.',
	];

	public function getDrivers()
	{
		return array_keys(self::$drivers);
	}

	public function getL10nDrivers()
	{
		$return = [];

		foreach (self::$drivers as $driver => $desc)
		{
			$return[$driver] => __($desc);
		}

		return $return;
	}
}
