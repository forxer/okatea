<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Misc;

use Tao\Forms\Statics\FormElements as form;
use Tao\Misc\ParametersHolder;

/**
 * Classe de base pour gérer des filtres de listes
 *
 */
class FiltersBase
{
	protected $id;
	protected $form_id;
	protected $config;
	protected $part='public';
	protected $defaults_params=array();

	protected $sess_prefix=array();

	public $params;
	public $fields=array();

	public function __construct($okt, $id, $config, $part, $params=array())
	{
		$this->okt = $okt;

		$this->id = $id;

		$this->config = $config;

		$this->setPart($part);

		$this->setDefaultParams();

		$params = array_merge($this->defaults_params,$params);

		$this->params = new ParametersHolder($params);

		$this->actives_filters = array();
	}

	public function getFilterFormId()
	{
		return $this->form_id;
	}

	public function getFilterSubmitName()
	{
		return $this->form_id.'_submit';
	}

	protected function setPart($part='public')
	{
		if ($part === 'admin')
		{
			$this->part = 'admin';

			$this->sess_prefix = 'sess_'.$this->id.'_fltr_admin_';
		}
		else {
			$this->part = 'public';

			$this->sess_prefix = 'sess_'.$this->id.'_fltr_public_';
		}

		$this->form_id = 'filters_form_'.$this->part.'_'.$this->id;
	}

	/**
	 * Set the default parameters.
	 *
	 * @return void
	 */
	protected function setDefaultParams()
	{
		if ($this->part === 'admin' && isset($this->config->admin_default_nb_per_page)) {
			$this->defaults_params['nb_per_page'] = $this->config->admin_default_nb_per_page;
		}
		elseif (isset($this->config->public_default_nb_per_page)) {
			$this->defaults_params['nb_per_page'] = $this->config->public_default_nb_per_page;
		}
	}

	/**
	 * Réinitialise les filtres
	 *
	 * @param $part
	 * @return boolean
	 */
	public function initFilters()
	{
		if (!empty($_SESSION))
		{
			$leng = strlen($this->sess_prefix);

			foreach ($_SESSION as $k=>$v)
			{
				$cur_prefix = substr($k,0,$leng);

				if ($cur_prefix == $this->sess_prefix) {
					unset($_SESSION[$k]);
				}
			}
		}
	}

	public function getFilters()
	{
		# page
		$this->setFilterPage();

		# number per page
		$this->setFilterNbPerPage();
	}

	protected function setFilter($name)
	{
		if (isset($_GET[$name]))
		{
			$this->params->$name = $_GET[$name];
			$_SESSION[$this->sess_prefix.$name] = $this->params->$name;

			$this->setActiveFilter($name);
		}
		elseif (isset($_SESSION[$this->sess_prefix.$name]))
		{
			$this->params->$name = $_SESSION[$this->sess_prefix.$name];

			$this->setActiveFilter($name);
		}
	}

	protected function setIntFilter($name)
	{
		if (isset($_GET[$name]) && $_GET[$name] != -1)
		{
			$this->params->$name = intval($_GET[$name]);
			$_SESSION[$this->sess_prefix.$name] = $this->params->$name;

			$this->setActiveFilter($name);
		}
		elseif (isset($_SESSION[$this->sess_prefix.$name]))
		{
			$this->params->$name = $_SESSION[$this->sess_prefix.$name];

			$this->setActiveFilter($name);
		}
	}

	protected function setCheckboxFilter($name)
	{
		if (isset($_GET[$name]))
		{
			$this->params->$name = intval($_GET[$name]);
			$_SESSION[$this->sess_prefix.$name] = $this->params->$name;

			$this->setActiveFilter($name);
		}
		elseif (isset($_GET[$this->getFilterSubmitName()])) {
			$this->params->$name = 0;
			if (isset($_SESSION[$this->sess_prefix.$name])) {
				unset($_SESSION[$this->sess_prefix.$name]);
			}
		}
		elseif (isset($_SESSION[$this->sess_prefix.$name]))
		{
			$this->params->$name = $_SESSION[$this->sess_prefix.$name];

			$this->setActiveFilter($name);
		}
	}

	protected function setActiveFilter($name)
	{
		if ($this->params->$name != $this->defaults_params[$name])
		{
			$this->params->show_filters = true;
			$this->actives_filters[] = $name;

			return true;
		}

		return false;
	}

	protected function isActiveFilter($name)
	{
		return in_array($name,$this->actives_filters);
	}

	public function getActiveClass($name, $class='active ui-state-active')
	{
		if ($this->isActiveFilter($name)) {
			return $class;
		}

		return '';
	}

	public function hasActiveFilter()
	{
		return (!empty($this->actives_filters));
	}

	protected function setFilterPage()
	{
		if (!empty($_GET['page']))
		{
			$this->params->page = intval($_GET['page']);
			$_SESSION[$this->sess_prefix.'page'] = $this->params->page;
		}
		elseif (isset($_SESSION[$this->sess_prefix.'page']))
		{
			$this->params->page = $_SESSION[$this->sess_prefix.'page'];
		}
		else {
			$this->params->page = 1;
		}
	}

	public function normalizePage($num_pages)
	{
		$num_pages = intval($num_pages);

		if ($num_pages > 0 && $this->params->page > $num_pages)
		{
			$this->params->page = $num_pages;

			$_SESSION[$this->sess_prefix.'page'] = $this->params->page;
		}
	}

	protected function setFilterNbPerPage()
	{
		if (isset($this->config->filters) && !$this->config->filters[$this->part]['nb_per_page']) {
			return null;
		}

		$this->setIntFilter('nb_per_page');

		$this->fields['nb_per_page'] = array(
			$this->form_id.'_nb_per_page',
			__('c_c_sorting_Number_per_page'),
			form::text(
				array('nb_per_page',$this->form_id.'_nb_per_page'),
				3,
				3,
				$this->params->nb_per_page,
				$this->getActiveClass('nb_per_page')
			)
		);
	}


	/* HTML
	------------------------------------------------*/

	/**
	 * Retourne le HTML des filtres
	 *
	 * @return string
	 */
	public function getFiltersFields(
		$bloc_format='<div class="four-cols">%s</div>',
		$item_format='<p class="col field"><label for="%s">%s</label>%s</p>')
	{
		$block = '';

		foreach ($this->fields as $field_id=>$field) {
			$block .= sprintf($item_format, $this->fields[$id][0], $this->fields[$id][1],$this->fields[$id][2]);
		}

		return sprintf($bloc_format,$block);
	}

	/**
	 * Retourne le HTML d'un filtre
	 *
	 * @param $id string
	 * @param $item_format string
	 * @return string
	 */
	public function getFilter($id,$item_format='<p class="col field"><label for="%s">%s</label>%s</p>')
	{
		if (isset($this->fields[$id])) {
			return sprintf($item_format, $this->fields[$id][0], $this->fields[$id][1],$this->fields[$id][2]);
		}
	}
}
