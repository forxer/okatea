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
 * Pile de messages d'information pour l'administration.
 *
 * @addtogroup Okatea
 *
 */
class Infos extends Stack
{
	/**
	 * Ajoute une information à la pile des informations.
	 *
	 * @param $msg string
	 * @return void
	 */
	public function set($msg)
	{
		$this->setItem($msg);
	}

	/**
	 * Formate et retourne les messages d'informations présents dans la pile.
	 *
	 * @param $format string
	 * @return string
	 */
	public function getInfos($format='<div class="infos_box">%s</div>')
	{
		return sprintf($format, parent::getHTML());
	}

	/**
	 * Indique si il y a des messages d'informations.
	 *
	 * @return boolean
	 */
	public function hasInfo()
	{
		return $this->hasItem();
	}
}
