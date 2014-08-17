<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Database\ConfigLayers\Dbal;

use Okatea\Tao\Database\ConfigLayers\DriverInterface;

class PdoSqlite implements DriverInterface
{
	protected $bSupported;

	public function isSupported()
	{
		if (null === $this->bSupported) {
			$this->bSupported = extension_loaded('pdo_sqlite');
		}

		return $this->bSupported;
	}

	public function getConfigFields()
	{
		return [
			[
				'id' => 'user',
				'type' => 'string',
				'label' => __('i_db_conf_db_username'),
				'default' => '',
				'required' => true
			],
			[
				'id' => 'password',
				'type' => 'string',
				'label' => __('i_db_conf_db_password'),
				'default' => '',
				'required' => true
			],
			[
				'id' => 'path',
				'type' => 'string',
				'label' => __('i_db_conf_db_sqlite_path'),
				'default' => '',
				'required' => false
			],
			[
				'id' => 'memory',
				'type' => 'boolean',
				'label' => __('i_db_conf_db_sqlite_memory'),
				'default' => false,
				'required' => false
			]
		];
	}
}
