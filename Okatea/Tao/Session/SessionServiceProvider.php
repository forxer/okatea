<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Session;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class SessionServiceProvider implements ServiceProviderInterface
{
	public function register(Container $okt)
	{
		$okt['flash'] = function($okt) {
			return new FlashMessages('okt_flashes');
		};

		$okt['session'] = function($okt) {
			return new Session(
				new NativeSessionStorage(
					[
						'cookie_lifetime' 	=> 0,
						'cookie_path' 		=> $okt['config']->app_url,
						'cookie_secure' 	=> $okt['request']->isSecure(),
						'cookie_httponly' 	=> true,
						'use_trans_sid' 	=> false,
						'use_only_cookies' 	=> true
					],
					new \SessionHandler()
				),
				null,
				$okt['flash'],
				$okt['csrf_token_name']
			);
		};
	}
}
