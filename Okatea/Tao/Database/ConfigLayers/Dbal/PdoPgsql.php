<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Database\ConfigLayers\Dbal;

use Okatea\Tao\Database\ConfigLayers\DriverInterface;

class PdoPgsql implements DriverInterface
{
	protected $bSupported;

	public function isSupported()
	{
		if (null === $this->bSupported) {
			$this->bSupported = extension_loaded('pdo_pgsql');
		}

		return $this->bSupported;
	}

	public function getConfigFields()
	{
		return [
			[
				'id' => 'host',
				'type' => 'string',
				'label' => __('i_db_conf_db_host'),
				'default' => '',
				'required' => true
			],
			[
				'id' => 'port',
				'type' => 'integer',
				'label' => __('i_db_conf_db_port'),
				'default' => '',
				'required' => false
			],
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
				'id' => 'dbname',
				'type' => 'string',
				'label' => __('i_db_conf_db_name'),
				'default' => '',
				'required' => true
			],
			[
				'id' => 'charset',
				'type' => 'string',
				'label' => __('i_db_conf_db_charset'),
				'default' => 'utf8',
				'required' => false
			],
			[
				'id' => 'sslmode',
				'type' => 'string',
				'label' => __('i_db_conf_db_pgsql_sslmode'),
				'default' => '',
				'required' => false
			]
		];
	}
}
