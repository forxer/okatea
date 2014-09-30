<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Templating;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TemplatingServiceProvider implements ServiceProviderInterface
{
	public function register(Container $okt)
	{
		$okt['tpl'] = function($okt) {
			return new Templating($okt);
		};
	}
}
