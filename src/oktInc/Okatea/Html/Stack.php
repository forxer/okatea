<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Html;

/**
 * Pour gérer une simple pile d'éléments et les retourner sous forme
 * de liste non-ordonnées.
 *
 * Note: Q&D
 */
class Stack
{
	protected $stack = array();


	/**
	 * Constructeur.
	 *
	 * @param array $items 		Les éléments de la pile
	 * @return void
	 */
	public function __construct($items=array())
	{
		$this->setItems($items);
	}

	/**
	 * Initialise la pile.
	 *
	 * @return void
	 */
	public function init()
	{
		$this->stack = array();
	}

	/**
	 * Ajoute un élément à la pile.
	 *
	 * @param string $str
	 * @return void
	 */
	public function setItem($str)
	{
		$this->stack[] = $str;
	}

	/**
	 * Ajoute un élément à la pile.
	 *
	 * @param array $items
	 * @return void
	 */
	public function setItems($items)
	{
		$this->stack = array_merge($this->stack, (array)$items);
	}

	/**
	 * Indique si il y a des éléments dans la pile
	 *
	 * @return boolean
	 */
	public function hasItem()
	{
		return !empty($this->stack);
	}

	public function __toString()
	{
		return $this->getHTML();
	}

	/**
	 * Construit le HTML à partir de la pile d'éléments.
	 *
	 * @return string
	 */
	public function getHTML()
	{
		if (!$this->hasItem()) {
			return null;
		}

		if (count($this->stack) > 1)
		{
			return
			'<ul><li>'.
			implode('</li><li>', array_map(array('\html','escapeHTML'),$this->stack))
			.'</li></ul>';
		}

		return '<p>'.\html::escapeHTML($this->stack[0]).'</p>';
	}

} # class htmlStack
