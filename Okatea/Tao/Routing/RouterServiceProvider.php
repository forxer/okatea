<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Routing;

use Okatea\Website\Router;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Routing\RequestContext;

class RouterServiceProvider implements ServiceProviderInterface
{
	public function register(Container $okt)
	{
		$okt['router'] = function($okt) {
			return new Router(
				$okt,
				$okt['config_dir'] . '/Routes',
				$okt['cache_dir'] . '/routing',
				$okt['debug'],
				$okt['logger']
			);
		};

		$okt['requestContext'] = function($okt) {
			$requestContext = new RequestContext();
			$requestContext->fromRequest($okt['request']);

			return $requestContext;
		};
	}
}
