<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Routing;

use Okatea\Admin\Router as adminRouter;
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
				$okt['config_path'] . '/Routes',
				$okt['cache_path'] . '/routing',
				$okt['debug'],
				$okt['logger']
			);
		};

		$okt['adminRouter'] = function($okt) {
			return new adminRouter(
				$okt,
				$okt['config_path'] . '/RoutesAdmin',
				$okt['cache_path'] . '/routing/admin',
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
