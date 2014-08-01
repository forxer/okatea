<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Services;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Monolog\Logger as Monolog;
use Monolog\Handler\FirePHPHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;

class Logger implements ServiceProviderInterface
{
	public function register(Container $okt)
	{
		$okt['logger'] = new Monolog('okatea', [
				new FirePHPHandler()
			],
			[
				new IntrospectionProcessor(),
				new WebProcessor(),
				new MemoryUsageProcessor(),
				new MemoryPeakUsageProcessor()
			]
		);
	}
}
