<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Core;

use Tao\Database\MySqli;

/**
 * Le gestionnaire de base de données.
 *
 */
class Connexion extends MySqli
{
	/**
	 * Stored instance for Singleton pattern.
	 *
	 * @var object mysql
	 */
	protected static $oInstance;

	/**
	 * Retourne l'instance de la classe. Singleton pattern.
	 *
	 */
	public static function getInstance()
	{
		if (!isset(self::$oInstance))
		{
			$oMysql = new Connexion();

			$oMysql->init(OKT_DB_USER, OKT_DB_PWD, OKT_DB_HOST, OKT_DB_NAME, OKT_DB_PREFIX);

			self::$oInstance = $oMysql;
		}

		return self::$oInstance;
	}

	/**
	 * Constructor disabled for Singleton pattern.
	 */
	private function __construct() { }

	/**
	 * Unable to use this in Singleton pattern.
	 */
	public function __clone()
	{
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}

	/**
	 * Unable to use this in Singleton pattern.
	 */
	public function __wakeup()
	{
		trigger_error('Unserializing is not allowed.', E_USER_ERROR);
	}
}
