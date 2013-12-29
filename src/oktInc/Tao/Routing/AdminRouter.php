<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Routing;

use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Router as BaseRouter;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Tao\Core\Application;

class AdminRouter extends BaseRouter
{
	use ControllerResolverTrait;

	/**
	 * @var Application
	 */
	protected $app;

	public function __construct(Application $app, $routes_file, $cache_dir = null, $debug = false, LoggerInterface $logger = null)
	{
		$this->app = $app;

		parent::__construct(
			new PhpFileLoader(new FileLocator($routes_file)),
			$routes_file,
			array(
				'cache_dir' => $cache_dir,
				'debug' => $debug
			),
			$app->requestContext,
			$logger
		);

	}


}