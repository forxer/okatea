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
	protected static $aCustoms = [];

	protected static $aSupported;

	/**
	 * Return list of all drivers.
	 *
	 * @return array
	 */
	public static function getAll()
	{
		return array_keys(array_merge(self::getDoctrineDBALDrivers(), self::$aCustoms));
	}

	/**
	 * Return list of all drivers with them support test.
	 *
	 * @return array
	 */
	public static function getAllWithTest()
	{
		return array_merge(self::getDoctrineDBALDrivers(), self::$aCustoms);
	}

	/**
	 * Return list of drivers supported by the environment.
	 *
	 * @return array
	 */
	public static function getSupported()
	{
		if (null === self::$aSupported)
		{
			self::$aSupported = [];

			foreach (self::getAllWithTest() as $sDriver => $sTest)
			{
				if ($sTest()) {
					self::$aSupported[] = $sDriver;
				}
			}
		}

		return self::$aSupported;
	}

	public static function getUnsupported()
	{
		return array_diff_assoc(self::getAll(), self::getSupported());
	}

	/**
	 * Indicates whether a given driver is supy the environment.
	 *
	 * @param string $sDriver
	 * @return boolean
	 */
	public static function isSupported($sDriver)
	{
		return in_array($sDriver, self::getSupported());
	}

	/**
	 * Return the first driver of the supported drivers list.
	 *
	 * @return string
	 */
	public static function getFirstSupported()
	{
		return isset(self::getSupported()[0]) ? self::getSupported()[0] : null;
	}

	/**
	 * Add a driver.
	 *
	 * @param string $sName
	 * @param callable $cTest
	 */
	public static function addDriver($sName, callable $cTest)
	{
		self::$aCustoms[$sName] = $cTest;
	}

	/**
	 * Return list of Doctrine DBAL built-in driver implementation.
	 *
	 * @var array
	 */
	protected static function getDoctrineDBALDrivers()
	{
		return [
			'pdo_mysql'           => function() { return extension_loaded("pdo_mysql"); },
			'drizzle_pdo_mysql'   => function() { return extension_loaded("pdo_mysql"); },
			'mysqli'              => function() { return extension_loaded("mysqli"); },
			'pdo_sqlite'          => function() { return extension_loaded("pdo_sqlite"); },
			'pdo_pgsql'           => function() { return extension_loaded("pdo_pgsql"); },
			'pdo_oci'             => function() { return extension_loaded("pdo_oci"); },
			'oci8'                => function() { return extension_loaded("oci8"); },
			'pdo_sqlsrv'          => function() { return extension_loaded("pdo_sqlsrv"); },
			'sqlsrv'              => function() { return extension_loaded("sqlsrv"); },
			'sqlanywhere'         => function() { return extension_loaded("sqlanywhere"); }
		];
	}
}
