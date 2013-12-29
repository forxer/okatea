<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Admin\Messages;

use Tao\Html\Stack;

/**
 * Pile de messages de succès pour l'administration.
 *
 * @addtogroup Okatea
 *
 */
class Success extends Stack
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
	public function getSuccess($format='<div class="success_box">%s</div>')
	{
		return $this->hasSuccess() ? sprintf($format,parent::getHTML()) : null;
	}

	/**
	 * Indique si il y a des messages
	 *
	 * @return boolean
	 */
	public function hasSuccess()
	{
		return $this->hasItem();
	}
}
