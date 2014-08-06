<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Session;

use Okatea\Tao\Messages\MessagesInterface;
//use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag;

/**
 * The class to handle the usual flash messages.
 */
class FlashMessages extends AutoExpireFlashBag implements MessagesInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function info($sMessage)
	{
		$this->add(self::TYPE_INFO, $sMessage);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getInfo(array $aDefault = [])
	{
		return $this->get(self::TYPE_INFO, $aDefault);
	}

	/**
	 * Gets messages of type "info" (read only).
	 *
	 * @param array  $aDefault Default value if "info" does not exist.
	 * @return array
	 */
	public function peekInfo(array $aDefault = [])
	{
		return $this->peek(self::TYPE_INFO, $aDefault);
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasInfo()
	{
		return $this->has(self::TYPE_INFO);
	}

	/**
	 * {@inheritdoc}
	 */
	public function success($sMessage)
	{
		$this->add(self::TYPE_SUCCESS, $sMessage);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSuccess(array $aDefault = [])
	{
		return $this->get(self::TYPE_SUCCESS, $aDefault);
	}

	/**
	 * Gets messages of type "success" (read only).
	 *
	 * @param array  $aDefault Default value if "success" does not exist.
	 * @return array
	 */
	public function peekSuccess(array $aDefault = [])
	{
		return $this->peek(self::TYPE_SUCCESS, $aDefault);
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasSuccess()
	{
		return $this->has(self::TYPE_SUCCESS);
	}

	/**
	 * {@inheritdoc}
	 */
	public function warning($sMessage)
	{
		$this->add(self::TYPE_WARNING, $sMessage);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getWarning(array $aDefault = [])
	{
		return $this->get(self::TYPE_WARNING, $aDefault);
	}

	/**
	 * Gets messages of type "warning" (read only).
	 *
	 * @param array  $aDefault Default value if "warning" does not exist.
	 * @return array
	 */
	public function peekWarning(array $aDefault = [])
	{
		return $this->peek(self::TYPE_WARNING, $aDefault);
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasWarning()
	{
		return $this->has(self::TYPE_WARNING);
	}

	/**
	 * {@inheritdoc}
	 */
	public function error($sMessage)
	{
		$this->add(self::TYPE_ERROR, $sMessage);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getError(array $aDefault = [])
	{
		return $this->get(self::TYPE_ERROR, $aDefault);
	}

	/**
	 * Gets messages of type "error" (read only).
	 *
	 * @param array  $aDefault Default value if "error" does not exist.
	 * @return array
	 */
	public function peekError(array $aDefault = [])
	{
		return $this->peek(self::TYPE_ERROR, $aDefault);
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasError()
	{
		return $this->has(self::TYPE_ERROR);
	}
}
