<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Database;

use Doctrine\DBAL\DriverManager as Dbal;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DatabaseServiceProvider implements ServiceProviderInterface
{
	public function register(Container $okt)
	{
		$okt['db'] = function($okt) {
			return Dbal::getConnection($okt['config']->database_configuration);
		};
	}
}
