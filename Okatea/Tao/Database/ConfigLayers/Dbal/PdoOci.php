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
		return [];
	}
}