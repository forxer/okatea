<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Users;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class UsersServiceProvider implements ServiceProviderInterface
{
	public function register(Container $okt)
	{
		$okt['visitor'] = function($okt) {
			return new Visitor(
				$okt,
				$okt['cookie_auth_name'],
				$okt['cookie_auth_from'],
				$okt['app_url'],
				$okt['request']->getHttpHost(),
				$okt['request']->isSecure()
			);
		};

		$okt['users'] = function($okt) {
			return new Users($okt);
		};

		$okt['groups'] = function($okt) {
			return new Groups($okt);
		};
	}
}
