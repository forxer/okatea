<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Services;

use Doctrine\DBAL\DriverManager as Dbal;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class Database implements ServiceProviderInterface
{
	public function register(Container $okt)
	{
		$okt['db'] = Dbal::getConnection($okt->config->database_configuration);
	}
}
