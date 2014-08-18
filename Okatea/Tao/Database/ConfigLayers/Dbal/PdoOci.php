<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Database\ConfigLayers\Dbal;

use Okatea\Tao\Database\ConfigLayers\DriverInterface;

class PdoOci implements DriverInterface
{
	protected $bSupported;

	public function isSupported()
	{
		if (null === $this->bSupported) {
			$this->bSupported = extension_loaded('pdo_oci');
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
				'id' => 'servicename',
				'type' => 'string',
				'label' => __('i_db_conf_db_oci_servicename'),
				'default' => '',
				'required' => false
			],
			[
				'id' => 'service',
				'type' => 'boolean',
				'label' => __('i_db_conf_db_oci_service'),
				'default' => false,
				'required' => false
			],
			[
				'id' => 'pooled',
				'type' => 'boolean',
				'label' => __('i_db_conf_db_oci_pooled'),
				'default' => false,
				'required' => false
			],
			[
				'id' => 'instancename',
				'type' => 'string',
				'label' => __('i_db_conf_db_oci_instancename'),
				'default' => '',
				'required' => false
			]
		];
	}
}
