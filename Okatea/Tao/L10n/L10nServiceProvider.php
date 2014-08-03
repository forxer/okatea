<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\L10n;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class L10nServiceProvider implements ServiceProviderInterface
{
	public function register(Container $okt)
	{
		$okt['languages'] = function($okt) {
			return new Languages($okt);
		};
	}
}
