<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Html;

/**
 * Permet de construire et d'afficher facilement une liste de vérifications.
 */
class Checklister
{

	/**
	 * Liste des vérifications.
	 * 
	 * @var array
	 */
	protected $check;

	/**
	 * URL de l'image ok.
	 * 
	 * @var string
	 */
	public $img_on;

	/**
	 * URL de l'image erreur.
	 * 
	 * @var string
	 */
	public $img_off;

	/**
	 * URL de l'image avertissement.
	 * 
	 * @var string
	 */
	public $img_wrn;

	/**
	 * Constucteur.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->check = array();
	}

	/**
	 * Permet d'ajouter un élément à la liste de vérification.
	 *
	 * @param
	 *        	string	name	Le nom de l'élément
	 * @param
	 *        	mixed	test	Test de l'élément, TRUE pour ok, FALSE pour échec ou NULL pour avertissement
	 * @param
	 *        	string	on		Le texte si succès
	 * @param
	 *        	string	off		Le texte si échec
	 * @return void
	 */
	public function addItem($name, $test, $on, $off)
	{
		$this->check[$name] = array(
			'test' => (($test === null) ? null : (boolean) $test),
			'on' => $on,
			'off' => $off
		);
	}

	/**
	 * Indique si tous les tests sont passés avec succès.
	 *
	 * @return boolean
	 */
	public function checkAll()
	{
		foreach ($this->check as $v)
		{
			if ($v['test'] === false)
			{
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Indique si un élément en particulier passe son test avec succès.
	 *
	 * @return boolean
	 */
	public function checkItem($name)
	{
		if (! empty($this->check[$name]))
		{
			return $this->check[$name]['test'];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Indique si il y a des avertissements.
	 *
	 * @return boolean
	 */
	public function checkWarnings()
	{
		foreach ($this->check as $v)
		{
			if ($v['test'] === null)
			{
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Affiche le HTML de la liste de vérifications.
	 *
	 * @param string $bloc
	 *        	de formatage du bloc de la liste
	 * @param string $item
	 *        	de formatage d'un élément de la liste
	 * @return string
	 */
	public function getHTML($bloc = '<ul class="checklist">%s</ul>', $item = '<li>%s</li>')
	{
		$res = '';
		foreach ($this->check as $k => $v)
		{
			if ($v['test'] === null)
			{
				$res .= sprintf($item, '<span class="icon error"></span> ' . $v['off']);
			}
			elseif ($v['test'] == false)
			{
				$res .= sprintf($item, '<span class="icon cross"></span> ' . $v['off']);
			}
			elseif ($v['test'])
			{
				$res .= sprintf($item, '<span class="icon tick"></span> ' . $v['on']);
			}
		}
		
		return sprintf($bloc, $res);
	}

	public function getLegend($bloc = '<ul class="checklistlegend">%s</ul>', $item = '<li>%s</li>')
	{
		$res = '';
		
		$res .= sprintf($item, '<span class="icon tick"></span> ' . __('c_c_checklist_valid'));
		
		$res .= sprintf($item, '<span class="icon error"></span> ' . __('c_c_checklist_warning'));
		
		$res .= sprintf($item, '<span class="icon cross"></span> ' . __('c_c_checklist_error'));
		
		return sprintf($bloc, $res);
	}
}
