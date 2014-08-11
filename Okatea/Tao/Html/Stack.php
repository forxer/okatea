<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Html;

/**
 * Pour gérer une simple pile d'éléments et les retourner sous forme
 * de liste non-ordonnées.
 *
 * Note: Q&D
 */
class Stack
{

	protected $aStack;

	/**
	 * Constructeur.
	 *
	 * @param array $items
	 *        	Les éléments de la pile
	 * @return void
	 */
	public function __construct($aItems = [])
	{
		$this->reset();
		
		if (is_array($aItems))
		{
			$this->setItems($aItems);
		}
	}

	/**
	 * Ré-initialise la pile.
	 *
	 * @return void
	 */
	public function reset()
	{
		$this->aStack = [];
	}

	/**
	 * Ajoute un élément à la pile.
	 *
	 * @param string $str        	
	 * @return void
	 */
	public function setItem($str)
	{
		$this->aStack[] = $str;
	}

	/**
	 * Ajoute des éléments à la pile.
	 *
	 * @param array $aItems        	
	 * @return void
	 */
	public function setItems($aItems)
	{
		if (!is_array($aItems))
		{
			return null;
		}
		
		$this->aStack = array_merge($this->aStack, $aItems);
	}

	/**
	 * Indique si il y a des éléments dans la pile
	 *
	 * @return boolean
	 */
	public function hasItem()
	{
		return !empty($this->aStack);
	}

	public function __toString()
	{
		$str = $this->getHTML();
		
		if (null === $str)
		{
			return '';
		}
		
		return $str;
	}

	/**
	 * Construit le HTML à partir de la pile d'éléments.
	 *
	 * @return string
	 */
	public function getHTML()
	{
		if (!$this->hasItem())
		{
			return null;
		}
		
		if (count($this->aStack) > 1)
		{
			$str = '<ul>';
			
			foreach ($this->aStack as $k => $v)
			{
				if (is_int($k))
				{
					$str .= '<li>' . $v . '</li>';
				}
				else
				{
					$str .= '<li>' . $k . ': ' . $v . '</li>';
				}
			}
			
			$str .= '</ul>';
			
			return $str;
		}
		else
		{
			$str = '<p>';
			
			foreach ($this->aStack as $k => $v)
			{
				if (is_int($k))
				{
					$str .= $v;
				}
				else
				{
					$str .= $k . ': ' . $v;
				}
			}
			
			$str .= '</p>';
		}
		
		return $str;
	}
}
