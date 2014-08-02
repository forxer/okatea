<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Config;

use Okatea\Tao\Cache\SingleFileCache;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ConfigServiceProvider implements ServiceProviderInterface
{
	public function register(Container $okt)
	{
		$okt['cacheConfig'] = function($okt) {
			return new SingleFileCache($okt->options->get('cache_dir') . '/static.php');
		};

		$okt['config'] = function($okt) {
			return $okt->newConfig('conf_site');
		};
	}
}
