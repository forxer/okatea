<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @class oktFlashMessages
 * @ingroup okt_classes_tools
 * @brief La classe pour gérer les messages flash
 *
 */
class oktFlashMessages
{
	protected $aMessagesTypes = array(
		'info',
		'success',
		'warning',
		'error'
	);

	protected $sSessionKey = 'okt_flash_messages';


	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct($sSessionKey=null)
	{
		if (!is_null($sSessionKey)) {
			$this->sSessionKey = (string)$sSessionKey;
		}

		if (!session_id()) {
			session_start();
		}

		if (!isset($_SESSION[$this->sSessionKey])) {
			$_SESSION[$this->sSessionKey] = array();
		}
	}

	/**
	 * Add a message $sMessage of type $sType to the queue.
	 *
	 * @param string $sMessage     	The message to add
	 * @param string $sType        	The type of message to add
	 * @return void
	 */
	public function addMessage($sMessage, $sType='info')
	{
		if (!isset($_SESSION[$this->sSessionKey])) {
			$_SESSION[$this->sSessionKey] = array();
		}

		if (!$this->isValidType($sType)) {
			$sType = 'info';
		}

		if (empty($_SESSION[$this->sSessionKey][$sType])) {
			$_SESSION[$this->sSessionKey][$sType] = array();
		}

		$_SESSION[$this->sSessionKey][$sType][] = $sMessage;
	}

	/**
	 * Add a message type "info" to the queue.
	 *
	 * @param  string   $sMessage     	The message
	 * @return  bool
	 */
	public function addInfo($sMessage)
	{
		return $this->addMessage($sMessage, 'info');
	}

	/**
	 * Add a message type "warning" to the queue.
	 *
	 * @param  string   $sMessage     	The message
	 * @return  bool
	 */
	public function addWarning($sMessage)
	{
		return $this->addMessage($sMessage, 'warning');
	}

	/**
	 * Add a message type "error" to the queue.
	 *
	 * @param  string   $sMessage     	The message
	 * @return  bool
	 */
	public function addError($sMessage)
	{
		return $this->addMessage($sMessage, 'error');
	}

	/**
	 * Add a message type "success" to the queue.
	 *
	 * @param  string   $sMessage     	The message
	 * @return  bool
	 */
	public function addSuccess($sMessage)
	{
		return $this->addMessage($sMessage, 'success');
	}

	/**
	 * Return the queued messages.
	 *
	 * @param  string   $sType     Which messages to display
	 * @return mixed
	 */
	public function getMessages($sType=null)
	{
		$aReturn = array();

		if (!isset($_SESSION[$this->sSessionKey])) {
			return null;
		}

		if (!is_null($sType))
		{
			if (!$this->isValidType($sType)) {
				return false;
			}

			if (empty($_SESSION[$this->sSessionKey][$sType])) {
				return null;
			}

			$aReturn = $_SESSION[$this->sSessionKey][$sType];

			$this->clear($sType);
		}
		else
		{
			$aReturn = $_SESSION[$this->sSessionKey];

			$this->clear();
		}

		return $aReturn;
	}

	/**
	 * Check to see if there are any ($sType) messages queued.
	 *
	 * @param  string   $sType     The type of messages to check for
	 * @return bool
	 */
	public function hasMessages($sType=null)
	{
		if (!isset($_SESSION[$this->sSessionKey])) {
			return false;
		}

		if (!is_null($sType))
		{
			if (!$this->isValidType($sType)) {
				return null;
			}

			if (!empty($_SESSION[$this->sSessionKey][$sType])) {
				return true;
			}
		}
		else
		{
			foreach ($this->aMessagesTypes as $sType)
			{
				if (!empty($_SESSION[$this->sSessionKey][$sType])) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Clear messages from the session data.
	 *
	 * @param string $sType 	The type of messages to clear
	 * @return bool
	 */
	public function clear($sType=null)
	{
		if (!is_null($sType) && $this->isValidType($sType)) {
			$_SESSION[$this->sSessionKey][$sType] = array();
		}
		else {
			$_SESSION[$this->sSessionKey] = array();
		}

		return true;
	}

	/**
	 * Check if $sType is a valid type.
	 *
	 * @param string $sType
	 * @return boolean
	 */
	protected function isValidType($sType)
	{
		return in_array($sType, $this->aMessagesTypes);
	}

	public function __toString()
	{
		return $this->display();
	}


} # class
