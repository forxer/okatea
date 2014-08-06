<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Navigation;

use Okatea\Tao\Navigation\Menus\Menus;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class NavigationServiceProvider implements ServiceProviderInterface
{
	public function register(Container $okt)
	{
		$okt['menus'] = function($okt) {
			return new Menus($okt);
		};
	}
}
