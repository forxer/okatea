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
 * Pile de messages d'erreurs pour l'administration.
 *
 * @addtogroup Okatea
 *
 */
class Errors extends Stack
{
	/**
	 * Ajoute une erreur à la pile des erreurs.
	 *
	 * @param $msg string
	 * @return void
	 */
	public function set($msg)
	{
		$this->setItem($msg);
	}

	/**
	 * Formate et retourne les erreurs présentes dans la pile.
	 *
	 * @param $format string
	 * @return string
	 */
	public function getErrors($format='<div class="errors_box">%s</div>')
	{
		return sprintf($format, parent::getHTML());
	}

	/**
	 * Indique si il y a des avertissements
	 *
	 * @return boolean
	 */
	public function hasError()
	{
		return $this->hasItem();
	}

} adminErrors
