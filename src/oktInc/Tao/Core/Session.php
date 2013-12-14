<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Core;

use Symfony\Component\HttpFoundation\Session\Session as BaseSession;

class Session extends BaseSession
{
	public function __construct(SessionStorageInterface $storage = null, AttributeBagInterface $attributes = null, FlashBagInterface $flashes = null)
	{
		parent::__construct($storage, $attributes, $flashes);

		$this->storage->setOptions(array(
			'use_trans_sid' => '0',
			'use_only_cookies' => '1'
		));

		$this->start();
	}
}
