<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Triggers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TriggersServiceProvider implements ServiceProviderInterface
{
	public function register(Container $okt)
	{
		$okt['triggers'] = function($okt) {
			return new Triggers();
		};
	}
}
