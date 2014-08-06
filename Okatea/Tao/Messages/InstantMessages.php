<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Messages;

class InstantMessages implements MessagesInterface
{
	protected $aMessages = [];

	/**
	 * Adds a message for type.
	 *
	 * @param string $sType
	 * @param string $sMessage
	 */
	public function add($sType, $sMessage)
	{
		$this->aMessages[$sType][] = $sMessage;
	}

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
		$aReturn = $aDefault;

		if ($this->hasInfo()) {
			$aReturn = $this->aMessages[self::TYPE_INFO];
		}

		return $aReturn;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasInfo()
	{
		return !empty($this->aMessages[self::TYPE_INFO]);
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
		$aReturn = $aDefault;

		if ($this->hasSuccess()) {
			$aReturn = $this->aMessages[self::TYPE_SUCCESS];
		}

		return $aReturn;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasSuccess()
	{
		return !empty($this->aMessages[self::TYPE_SUCCESS]);
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
		$aReturn = $aDefault;

		if ($this->hasWarning()) {
			$aReturn = $this->aMessages[self::TYPE_WARNING];
		}

		return $aReturn;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasWarning()
	{
		return !empty($this->aMessages[self::TYPE_WARNING]);
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
		$aReturn = $aDefault;

		if ($this->hasError()) {
			$aReturn = $this->aMessages[self::TYPE_ERROR];
		}

		return $aReturn;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasError()
	{
		return !empty($this->aMessages[self::TYPE_ERROR]);
	}
}
