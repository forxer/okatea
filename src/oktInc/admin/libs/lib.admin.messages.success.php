<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tao\Html\Stack;

/**
 * Pile de messages de succès pour l'administration.
 *
 * @addtogroup Okatea
 *
 */
class adminMessagesSuccess extends Stack
{
	/**
	 * Ajoute un message à la pile de messages.
	 *
	 * @param $msg string
	 * @return void
	 */
	public function set($msg)
	{
		$this->setItem($msg);
	}

	/**
	 * Formate et retourne les messages présents dans la pile.
	 *
	 * @param $format string
	 * @return string
	 */
	public function getMessages($format='<div class="messages_box">%s</div>')
	{
		return sprintf($format,parent::getHTML());
	}

	/**
	 * Indique si il y a des messages
	 *
	 * @return boolean
	 */
	public function hasMessage()
	{
		return $this->hasItem();
	}

	/**
	 * Ajoute un message si le test est vrai.
	 *
	 * @param $test boolean
	 * @param $msg string
	 * @return void
	 */
	public function success($test,$msg)
	{
		if (isset($_GET[$test])) {
			$this->set($msg);
		}
	}

} # class messages
