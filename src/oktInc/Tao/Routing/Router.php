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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Router as BaseRouter;
use Tao\Core\Application;
use Tao\Routing\Loader\YamlDirectoryLoader;

class Router extends BaseRouter
{
	use ControllerResolverTrait;

	/**
	 * @var Application
	 */
	protected $app;

	public function __construct(Application $app, $ressources_dir, $cache_dir = null, $debug = false, LoggerInterface $logger = null)
	{
		$this->app = $app;

		# restrict to the default language if we have only one language
		if ($this->app->languages->unique) {
			$ressources_dir .= '/'.$this->app->config->language;
		}

		parent::__construct(
			new YamlDirectoryLoader(
				$app,
				new FileLocator($ressources_dir
			)),
			$ressources_dir,
			array(
				'cache_dir' => $cache_dir,
				'debug' => $debug
			),
			$app->requestContext,
			$logger
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function generate($name, $parameters = array(), $language = null, $referenceType = self::ABSOLUTE_PATH)
	{
		if (!$this->app->languages->unique)
		{
			if (null === $language) {
				$name = $name.'-'.$this->app->user->language;
			}
			else {
				$name = $name.'-'.$language;
			}
		}

		return $this->getGenerator()->generate($name, $parameters, $referenceType);
	}

	/**
	 * Touch collection resources to force cache regenerating.
	 *
	 */
	public function touchResources()
	{
		$aResources = array();
		foreach ($this->getRouteCollection()->getResources() as $oResource) {
			$aResources[] = (string)$oResource;
		}

		$fs = new Filesystem();
		$fs->touch($aResources);
	}
}
