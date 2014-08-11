<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Routing\Loader;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\Routing\Loader\YamlFileLoader as BaseYamlFileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * YamlDirectoryLoader loads routing information
 * from Yaml routing files.
 */
class YamlDirectoryLoader extends BaseYamlFileLoader
{

	/**
	 * Loads from Yaml file from a directory.
	 *
	 * @param string $path
	 *        	A directory path
	 * @param string|null $type
	 *        	The resource type
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
		usort($files, function (\SplFileInfo $a, \SplFileInfo $b)
		{
			return (string) $a > (string) $b ? 1 : - 1;
		});
		
		foreach ($files as $file)
		{
			if (!$file->isFile() || '.yml' !== substr($file->getFilename(), - 4))
			{
				continue;
			}
			
			$collection->addCollection(parent::load($file->getRealPath(), $type));
		}
		
		return $collection;
	}

	/**
	 * @ERROR!!!
	 */
	public function supports($resource, $type = null)
	{
		try
		{
			$path = $this->locator->locate($resource);
		}
		catch (\Exception $e)
		{
			return false;
		}
		
		return is_string($resource) && is_dir($path) && (!$type || 'yaml' === $type);
	}
}
