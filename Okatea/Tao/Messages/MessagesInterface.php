<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Messages;

interface MessagesInterface
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
	public function info($sMessage);

	/**
	 * Gets and clears message type "info" from the stack.
	 *
	 * @param array  $aDefault Default value if "info" does not exist.
	 * @return array
	 */
	public function getInfo(array $aDefault = []);

	/**
	 * Returns true if message type "info" exists, false if not.
	 *
	 * @return boolean
	 */
	public function hasInfo();

	/**
	 * Add a message type "success" to the stack.
	 *
	 * @param string $sMessage The message
	 * @return void
	 */
	public function success($sMessage);

	/**
	 * Gets and clears message type "success" from the stack.
	 *
	 * @param array  $aDefault Default value if "success" does not exist.
	 * @return array
	 */
	public function getSuccess(array $aDefault = []);

	/**
	 * Returns true if message type "success" exists, false if not.
	 *
	 * @return boolean
	 */
	public function hasSuccess();

	/**
	 * Add a message type "warning" to the stack.
	 *
	 * @param string $sMessage The message
	 * @return void
	 */
	public function warning($sMessage);

	/**
	 * Gets and clears message type "warning" from the stack.
	 *
	 * @param array  $aDefault Default value if "warning" does not exist.
	 * @return array
	 */
	public function getWarning(array $aDefault = []);

	/**
	 * Returns true if message type "warning" exists, false if not.
	 *
	 * @return boolean
	 */
	public function hasWarning();

	/**
	 * Add a message type "error" to the stack.
	 *
	 * @param string $sMessage The message
	 * @return void
	 */
	public function error($sMessage);

	/**
	 * Gets and clears message type "error" from the stack.
	 *
	 * @param array  $aDefault Default value if "error" does not exist.
	 * @return array
	 */
	public function getError(array $aDefault = []);

	/**
	 * Returns true if message type "error" exists, false if not.
	 *
	 * @return boolean
	 */
	public function hasError();
}
