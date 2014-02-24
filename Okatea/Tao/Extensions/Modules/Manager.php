<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Extensions\Modules;

use Okatea\Tao\Extensions\Manager as BaseManager;

class Manager extends BaseManager
{
	public function addExtension($id, $version, $name = '', $desc = '', $author = '', $priority = 1000, $status = 0, $type = 'module')
	{
		return parent::addExtension($id, $version, $name, $desc, $author, $priority, $status, $type);
	}

	public function getFromDatabase(array $aParams = array())
	{
		$aParams['type'] = 'module';
		return parent::getFromDatabase($aParams);
	}
}
