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
use Tao\Routing\Loader\YamlDirectoryLoader;

class AdminRouter extends BaseRouter
{
	use ControllerResolverTrait;

	/**
	 * @var Application
	 */
	protected $app;

	/**
	 *
	 */
	public function __construct(Application $app, $ressources_dir, $cache_dir = null, $debug = false, LoggerInterface $logger = null)
	{
		$this->app = $app;

		parent::__construct(
			new YamlDirectoryLoader(new FileLocator($ressources_dir)),
			$ressources_dir,
			array(
				'cache_dir' => $cache_dir,
				'debug' => $debug,
				'generator_cache_class'  => 'OkateaAdminUrlGenerator',
				'matcher_cache_class'    => 'OkateaAdminUrlMatcher'
			),
			$app->requestContext,
			$logger
		);
	}

	/**
	 * Check if a named route exists.
	 *
	 * @return boolean
	 */
	public function routeExists($sRouteName)
	{
		return (null === $this->getRouteCollection()->get($sRouteName)) ? false : true;
	}

}