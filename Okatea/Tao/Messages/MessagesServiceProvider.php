<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Messages;

use Okatea\Tao\Session\FlashMessages;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class MessagesServiceProvider implements ServiceProviderInterface
{
	public function register(Container $okt)
	{
		$okt['flashMessages'] = function() {
			return new FlashMessages('okt_flashes');
		};

		$okt['instantMessages'] = function() {
			return new InstantMessages();
		};

		$okt['messages'] = function($okt) {
			return new Messages($okt);
		};
	}
}
