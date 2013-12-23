<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Misc;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

/**
 * La classe pour gérer les messages flash
 *
 */
class FlashMessages extends FlashBag
{
	/**
	 * Add a message type "info" to the queue.
	 *
	 * @param  string   $sMessage     	The message
	 * @return  void
	 */
	public function info($sMessage)
	{
		$this->add('infos', $sMessage);
	}

	/**
	 * Add a message type "success" to the queue.
	 *
	 * @param  string   $sMessage     	The message
	 * @return  void
	 */
	public function success($sMessage)
	{
		$this->add('success', $sMessage);
	}

	/**
	 * Add a message type "warning" to the queue.
	 *
	 * @param  string   $sMessage     	The message
	 * @return  void
	 */
	public function warning($sMessage)
	{
		$this->add('warnings', $sMessage);
	}

	/**
	 * Add a message type "error" to the queue.
	 *
	 * @param  string   $sMessage     	The message
	 * @return  void
	 */
	public function error($sMessage)
	{
		$this->add('errors', $sMessage);
	}

}