<?php
/**
 * Pile d'avertissements pour l'administration.
 *
 * @addtogroup Okatea
 *
 */

class adminWarnings extends htmlStack
{
	/**
	 * Ajoute un avertissement à la pile des avertissements.
	 *
	 * @param $msg string
	 * @return void
	 */
	public function set($msg)
	{
		$this->setItem($msg);
	}

	/**
	 * Formate et retourne les avertissements présents dans la pile.
	 *
	 * @param $format string
	 * @return string
	 */
	public function getWarnings($format='<div class="warnings_box">%s</div>')
	{
		return sprintf($format,parent::getHTML());
	}

	/**
	 * Indique si il y a des avertissements
	 *
	 * @return boolean
	 */
	public function hasWarning()
	{
		return $this->hasItem();
	}

} # class warnings
