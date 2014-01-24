<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Routing\Loader;

use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\RouteCollection;

/**
 * PhpDirectoryLoader loads routing information
 * from PHP routing files.
 *
 */
class PhpDirectoryLoader extends PhpFileLoader
{
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
		$collection->addResource(new DirectoryResource($dir, '/\.php$/'));
		$files = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::LEAVES_ONLY));
		usort($files, function (\SplFileInfo $a, \SplFileInfo $b) {
			return (string) $a > (string) $b ? 1 : -1;
		});

		foreach ($files as $file)
		{
			if (!$file->isFile() || '.php' !== substr($file->getFilename(), -4)) {
				continue;
			}

			$collection->addCollection(parent::load($file->getRealPath(), $type));
		}

		return $collection;
	}


}
