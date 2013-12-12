<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Routing\Loader;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Loader\YamlFileLoader as BaseYamlFileLoader;
use Tao\Core\Application;

class YamlFileLoader extends BaseYamlFileLoader
{
	/**
	 * @var Application
	 */
	protected $app;

	public function __construct(Application $app, FileLocatorInterface $locator)
	{
		$this->locator = $locator;

		$this->app = $app;
	}

	/**
	 * Parses a route and adds it to the RouteCollection.
	 *
	 * @param RouteCollection $collection A RouteCollection instance
	 * @param string          $name       Route name
	 * @param array           $config     Route definition
	 * @param string          $path       Full path of the YAML file being processed
	 */
	protected function parseRoute(RouteCollection $collection, $name, array $config, $path)
	{
		$defaults = isset($config['defaults']) ? $config['defaults'] : array();
		$requirements = isset($config['requirements']) ? $config['requirements'] : array();
		$options = isset($config['options']) ? $config['options'] : array();
		$host = isset($config['host']) ? $config['host'] : '';
		$schemes = isset($config['schemes']) ? $config['schemes'] : array();
		$methods = isset($config['methods']) ? $config['methods'] : array();
		$condition = isset($config['condition']) ? $config['condition'] : null;

		# remove language code from default language routes
		if ($this->app->languages->unique) {
			$config['path'] = substr($config['path'], 0, 1+strlen($this->app->config->language));
		}

		$route = new Route($config['path'], $defaults, $requirements, $options, $host, $schemes, $methods, $condition);

		$collection->add($name, $route);
	}
}
