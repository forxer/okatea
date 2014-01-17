<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Admin;

use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Router as BaseRouter;
use Okatea\Tao\Application;
use Okatea\Tao\Routing\Loader\YamlDirectoryLoader;
use Okatea\Tao\Routing\ControllerResolverTrait;

class Router extends BaseRouter
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
	 * This is a quicky dirty hack to link from the admin to the website.
	 *
	 * @TODO : need to extends Symfony\Component\Routing\Generator\UrlGenerator
	 */
	public function generateFromWebsite($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
	{
		return str_replace(
			$this->app->config->app_path,
			$this->app->config->app_path.'admin/',
			$this->getGenerator()->generate($name, $parameters, $referenceType)
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