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
	protected $aDrivers = [];

	public function __construct(array $aCustom = [])
	{
		foreach (self::getDoctrineDBALDrivers() as $sDriver => $sClass) {
			$this->aDrivers[$sDriver] = new $sClass;
		}

		if (!empty($aCustom))
		{
			foreach ($aCustom as $sDriver => $sClass) {
				$this->aDrivers[$sDriver] = new $sClass;
			}
		}
	}

	/**
	 * Return list of all drivers.
	 *
	 * @return array
	 */
	public function getDrivers()
	{
		return $this->aDrivers;
	}

	/**
	 * Add a driver.
	 *
	 * @param string $sName
	 * @param string $sClass
	 */
	public function addDriver($sName, $sClass)
	{
		$this->aDrivers[$sName] = new $sClass;
	}

	/**
	 * Return list of Doctrine DBAL built-in driver implementation.
	 *
	 * @var array
	 */
	protected static function getDoctrineDBALDrivers()
	{
		return [
			'pdo_mysql'           => 'Okatea\Tao\Database\ConfigLayers\Dbal\PdoMysql',
			'drizzle_pdo_mysql'   => 'Okatea\Tao\Database\ConfigLayers\Dbal\DrizzlePdoMysql',
			'mysqli'              => 'Okatea\Tao\Database\ConfigLayers\Dbal\Mysqli',
			'pdo_sqlite'          => 'Okatea\Tao\Database\ConfigLayers\Dbal\PdoSqlite',
			'pdo_pgsql'           => 'Okatea\Tao\Database\ConfigLayers\Dbal\PdoPgsql',
			'pdo_oci'             => 'Okatea\Tao\Database\ConfigLayers\Dbal\PdoOci',
			'oci8'                => 'Okatea\Tao\Database\ConfigLayers\Dbal\Oci8',
			'pdo_sqlsrv'          => 'Okatea\Tao\Database\ConfigLayers\Dbal\PdoSqlsrv',
			'sqlsrv'              => 'Okatea\Tao\Database\ConfigLayers\Dbal\Sqlsrv',
			'sqlanywhere'         => 'Okatea\Tao\Database\ConfigLayers\Dbal\SqlAnywhere'
		];
	}
}
