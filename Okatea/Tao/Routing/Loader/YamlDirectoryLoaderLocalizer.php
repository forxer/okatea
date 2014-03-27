<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Routing\Loader;

use Okatea\Tao\Application;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\Routing\Loader\YamlFileLoader as BaseYamlFileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * YamlDirectoryLoader loads routing information
 * from Yaml routing files.
 *
 */
class YamlDirectoryLoaderLocalizer extends BaseYamlFileLoader
{
	/**
	 * @var Application
	 */
	protected $app;

	public function __construct(Application $app, FileLocatorInterface $locator)
	{
		$this->app = $app;

		parent::__construct($locator);
	}

	/**
	 * Loads from Yaml file from a directory.
	 *
	 * @param string      $path A directory path
	 * @param string|null $type The resource type
	 *
	 * @return RouteCollection A RouteCollection instance
	 *
	 * @throws \InvalidArgumentException When the directory does not exist or its routes cannot be parsed
	 */
	public function load($path, $type = null)
	{
		$dir = $this->locator->locate($path);

		$collection = new RouteCollection();
		$collection->addResource(new DirectoryResource($dir, '/\.yml$/'));
		$files = iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::LEAVES_ONLY));
		usort($files, function (\SplFileInfo $a, \SplFileInfo $b) {
			return (string) $a > (string) $b ? 1 : -1;
		});

		foreach ($files as $file)
		{
			if (!$file->isFile() || '.yml' !== substr($file->getFilename(), -4)) {
				continue;
			}

			$this->language = basename(dirname($file->getPathname()));

			$collection->addCollection(parent::load($file->getRealPath(), $type));
		}

		return $collection;
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

		if (!$this->app->languages->unique)
		{
			$name .= '-'.$this->language;
			$config['path'] = '/'.$this->language.$config['path'];
		}

		$route = new Route($config['path'], $defaults, $requirements, $options, $host, $schemes, $methods, $condition);

		$collection->add($name, $route);
	}

	/**
	 * {@inheritdoc}
	 */
	public function supports($resource, $type = null)
	{
		try {
			$path = $this->locator->locate($resource);
		} catch (\Exception $e) {
			return false;
		}

		return is_string($resource) && is_dir($path) && (!$type || 'yaml' === $type);
	}
}
