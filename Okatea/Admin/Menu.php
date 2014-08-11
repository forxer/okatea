<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Admin;

use Okatea\Tao\Html\Escaper;

/**
 * Permet de construire les menus de l'administration.
 */
class Menu
{

	/**
	 * Identifiant du bloc.
	 *
	 * @access protected
	 * @var string
	 */
	protected $id;

	/**
	 * Chaines modèles HTML du bloc.
	 *
	 * @access protected
	 * @var array
	 */
	protected $html;

	/**
	 * La pile d'éléments
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * Le nombre d'élément dans la pile.
	 *
	 * @access private
	 * @var integer
	 */
	protected $num;

	/**
	 * Constructeur.
	 *
	 * @param
	 *        	integer	id			L'identifiant du menu à construire
	 * @param
	 *        	array	html		Eléments HTML du menu
	 * @return void
	 */
	public function __construct($id = null, array $html = [])
	{
		$this->id = $id;
		$this->items = [];
		$this->num = 0;
		$this->setDefaultHtml($html);
	}

	protected function setDefaultHtml(array $html = [])
	{
		$this->html = [];
		$this->html = array_merge(array(
			'block' => '<ul%2$s>%1$s</ul>',
			'item' => '<li%3$s><a href="%2$s">%1$s</a>%4$s</li>',
			'active' => '<li%3$s class="actif"><a href="%2$s">%1$s</a>%4$s</li>',
			'separator' => '',
			'emptyBlock' => '<p%s>&nbsp;</p>'
		), $html);
	}

	public function setHtml($html, $str)
	{
		if (array_key_exists($html, $this->html))
		{
			$this->html[$html] = $str;
		}
	}

	/**
	 * Permet d'ajouter un élément au bloc.
	 *
	 * @param string $title
	 *        	l'élément
	 * @param string $url
	 *        	l'élément
	 * @param boolean $active
	 *        	l'élément est actuellement actif (false)
	 * @param position $position
	 *        	de l'élément dans le menu ('')
	 * @param boolean $show
	 *        	l'élément doit être affiché (true)
	 * @param integer $id
	 *        	l'élément (null)
	 * @param mixed $sub
	 *        	(null)
	 * @param string $icon
	 *        	icone (null)
	 * @return void
	 */
	public function add($title, $url = '', $active = false, $position = '', $show = true, $id = null, $sub = null, $icon = null)
	{
		if ($show)
		{
			$this->items[$this->num ++] = array(
				'id' => $id,
				'title' => $title,
				'url' => $url,
				'active' => $active,
				'position' => intval($position),
				'sub' => $sub,
				'icon' => $icon
			);
		}
	}

	public function __set($title, $url = '')
	{
		$this->add($title, $url);
	}

	public function getItems()
	{
		usort($this->items, function ($a, $b)
		{
			if ($a['position'] == $b['position'])
				return 0;
			return ($a['position'] > $b['position']) ? 1 : - 1;
		});
		
		return $this->items;
	}

	/**
	 * Retourne le bloc formaté en HTML dans un tableau à deux clé.
	 */
	public function build()
	{
		if ($this->num > 0)
		{
			usort($this->items, function ($a, $b)
			{
				if ($a['position'] == $b['position'])
					return 0;
				return ($a['position'] > $b['position']) ? 1 : - 1;
			});
			
			$res = [];
			$active = null;
			
			for ($i = 0; $i < $this->num; $i ++)
			{
				$this->items[$i]['i'] = $i;
				
				$sub = array(
					'html' => null,
					'active' => null
				);
				
				if ($this->items[$i]['sub'] !== null && $this->items[$i]['sub'] instanceof Menu)
				{
					$sub = $this->items[$i]['sub']->build();
				}
				
				if ($this->items[$i]['active'] || $sub['active'] !== null)
				{
					$this->items[$i]['active'] = true;
					$active = $i;
				}
				
				$res[] = sprintf(($this->items[$i]['active'] ? $this->html['active'] : $this->html['item']), Escaper::html($this->items[$i]['title']), $this->items[$i]['url'], ($this->items[$i]['id'] !== null ? ' id="' . $this->items[$i]['id'] . '"' : ''), $sub['html']);
			}
			
			return array(
				'html' => sprintf($this->html['block'], implode($this->html['separator'], $res), ($this->id !== null ? ' id="' . $this->id . '"' : '')),
				'active' => $active
			);
		}
		else
		{
			return array(
				'html' => sprintf($this->html['emptyBlock'], ($this->id !== null ? ' id="' . $this->id . '"' : '')),
				'active' => null
			);
		}
	}
}
