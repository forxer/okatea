<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Database\ConfigLayers;

interface DriverInterface
{
	/**
	 * Indicate if the driver is supported by the environment.
	 *
	 * @return boolean
	 */
	public function isSupported();

	/**
	 * Return driver config fields.
	 *
	 * @return array
	 */
	public function getConfigFields();

}
