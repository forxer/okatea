<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Session;

//use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag;

/**
 * The class to handle the usual flash messages.
 */
class FlashMessages extends AutoExpireFlashBag
{
	const TYPE_INFO = 'info';
	const TYPE_SUCCESS = 'success';
	const TYPE_WARNING = 'warning';
	const TYPE_ERROR = 'error';

	/**
	 * Add a message type "info" to the stack.
	 *
	 * @param string $sMessage The message
	 * @return void
	 */
	public function info($sMessage)
	{
		$this->add(self::TYPE_INFO, $sMessage);
	}

	/**
	 * Gets and clears message type "info" from the stack.
	 *
	 * @return array
	 */
	public function getInfo()
	{
		return $this->get(self::TYPE_INFO, []);
	}

	/**
	 * Gets messages of type "info" (read only).
	 *
	 * @return array
	 */
	public function peekInfo()
	{
		return $this->peek(self::TYPE_INFO, []);
	}

	/**
	 * Returns true if message type "info" exists, false if not.
	 *
	 * @return boolean
	 */
	public function hasInfo()
	{
		return $this->has(self::TYPE_INFO);
	}

	/**
	 * Add a message type "success" to the stack.
	 *
	 * @param string $sMessage
	 *        	The message
	 * @return void
	 */
	public function success($sMessage)
	{
		$this->add(self::TYPE_SUCCESS, $sMessage);
	}

	/**
	 * Gets and clears message type "success" from the stack.
	 *
	 * @return array
	 */
	public function getSuccess()
	{
		return $this->get(self::TYPE_SUCCESS, []);
	}

	/**
	 * Gets messages of type "success" (read only).
	 *
	 * @return array
	 */
	public function peekSuccess()
	{
		return $this->peek(self::TYPE_SUCCESS, []);
	}

	/**
	 * Returns true if message type "success" exists, false if not.
	 *
	 * @return boolean
	 */
	public function hasSuccess()
	{
		return $this->has(self::TYPE_SUCCESS);
	}

	/**
	 * Add a message type "warning" to the stack.
	 *
	 * @param string $sMessage
	 *        	The message
	 * @return void
	 */
	public function warning($sMessage)
	{
		$this->add(self::TYPE_WARNING, $sMessage);
	}

	/**
	 * Gets and clears message type "warning" from the stack.
	 *
	 * @return array
	 */
	public function getWarning()
	{
		return $this->get(self::TYPE_WARNING, []);
	}

	/**
	 * Gets messages of type "warning" (read only).
	 *
	 * @return array
	 */
	public function peekWarning()
	{
		return $this->peek(self::TYPE_WARNING, []);
	}

	/**
	 * Returns true if message type "warning" exists, false if not.
	 *
	 * @return boolean
	 */
	public function hasWarning()
	{
		return $this->has(self::TYPE_WARNING);
	}

	/**
	 * Add a message type "error" to the stack.
	 *
	 * @param string $sMessage
	 *        	The message
	 * @return void
	 */
	public function error($sMessage)
	{
		$this->add(self::TYPE_ERROR, $sMessage);
	}

	/**
	 * Gets and clears message type "error" from the stack.
	 *
	 * @return array
	 */
	public function getError()
	{
		return $this->get(self::TYPE_ERROR, []);
	}

	/**
	 * Gets messages of type "error" (read only).
	 *
	 * @return array
	 */
	public function peekError()
	{
		return $this->peek(self::TYPE_ERROR, []);
	}

	/**
	 * Returns true if message type "error" exists, false if not.
	 *
	 * @return boolean
	 */
	public function hasError()
	{
		return $this->has(self::TYPE_ERROR);
	}
}
