<?php
##header##


class ##module_camel_case_id##Filters extends filters
{
	protected $get_items_params = array();

	protected $order_by_array = array();

	public function __construct($oConfig, $part='public', $params=array())
	{
		parent::__construct('##module_id##', $oConfig, $part, $params);
	}

	public function setDefaultParams()
	{
		$this->defaults_params = array(
			'show_filters' => false,

			'page' => 1,
			'nb_per_page' => 5,

			'visibility' => 2,
			'rubrique_id' => 0
		);

		if ($this->part === 'admin') {
			$this->defaults_params['order_by'] = $this->config->admin_default_order_by;
			$this->defaults_params['order_direction'] = $this->config->admin_default_order_direction;
		}
		else {
			$this->defaults_params['order_by'] = $this->config->public_default_order_by;
			$this->defaults_params['order_direction'] = $this->config->public_default_order_direction;
		}

		parent::setDefaultParams();
	}

	public function setParams(&$params=array())
	{
		$this->get_items_params =& $params;
	}

	/**
	 * Récupère les filtres
	 *
	 * @param $part
	 * @return void
	 */
	public function getFilters()
	{
		# tableau de type de tri de base
		$this->order_by_array = array();
		$this->order_by_array['date de création'] = 'created_at';

		if ($this->part === 'admin') {
			$this->order_by_array['date de modification'] = 'updated_at';
		}

		$this->order_by_array['titre'] = 'title';


		# page
		$this->setFilterPage();

		# number per page
		$this->setFilterNbPerPage();

		# visibilité (seulement sur l'admin)
		$this->setFilterVisibility();

		# ordre et sens du tri
		$this->setFilterOrderBy();
	}


	protected function setFilterVisibility()
	{
		if ($this->part !== 'admin') {
			return null;
		}

		if (!isset($this->get_items_params['visibility']))
		{
			$this->setIntFilter('visibility');
			$this->get_items_params['visibility'] = $this->params->visibility;
		}

		$this->fields['visibility'] = array(
			$this->form_id.'_visibility',
			'Visibilité',
			form::select(
				array('visibility', $this->form_id.'_visibility'),
				array('toutes visibilités'=>2, 'visibles'=>1, 'masqués'=>0),
				$this->get_items_params['visibility']
			)
		);
	}

	protected function setFilterOrderBy()
	{
		# ordre du tri
		if (isset($_GET['order_by']))
		{
			$this->params->order_by = $_GET['order_by'];
			$_SESSION[$this->sess_prefix.'order_by'] = $this->params->order_by;
			$this->params->show_filters = true;
		}
		elseif (isset($_SESSION[$this->sess_prefix.'order_by']))
		{
			$this->params->order_by = $_SESSION[$this->sess_prefix.'order_by'];
			$this->params->show_filters = true;
		}

		$this->fields['order_by'] = array(
			$this->form_id.'_order_by',
			__('c_c_sorting_Sorted_by'),
			form::select(
				array('order_by', $this->form_id.'_order_by'),
				$this->order_by_array,
				$this->params->order_by)
		);

		switch ($this->params->order_by)
		{
			default:
			case 'created_at':
				$this->get_items_params['order'] = 'p.created_at';
			break;

			case 'updated_at':
				$this->get_items_params['order'] = 'p.updated_at';
			break;

			case 'title':
				$this->get_items_params['order'] = 'p.title';
			break;
		}

		# sens du tri
		if (isset($_GET['order_direction']))
		{
			$this->params->order_direction = $_GET['order_direction'];
			$_SESSION[$this->sess_prefix.'order_direction'] = $this->params->order_direction;
			$this->params->show_filters = true;
		}
		elseif (isset($_SESSION[$this->sess_prefix.'order_direction']))
		{
			$this->params->order_direction = $_SESSION[$this->sess_prefix.'order_direction'];
			$this->params->show_filters = true;
		}

		$this->get_items_params['order_direction'] = $this->params->order_direction;

		$this->fields['order_direction'] = array(
			$this->form_id.'_order_direction',
			__('c_c_sorting_Sort_direction'),
			form::select(
				array('order_direction', $this->form_id.'_order_direction'),
				array(__('c_c_sorting_descending')=>'DESC',__('c_c_sorting_ascending')=>'ASC'),
				$this->params->order_direction)
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
		$bloc_format='<div class="three-cols">%s</div>',
		$item_format='<p class="col field"><label for="%s">%s</label>%s</p>')
	{
		$return = '';

		$block = '';

		$block .= $this->getFilter('visibility',$item_format);
		$block .= $this->getFilter('nb_per_page',$item_format);

		$return .= sprintf($bloc_format,$block);

		$block = '';

		$block .= $this->getFilter('order_by',$item_format);
		$block .= $this->getFilter('order_direction',$item_format);

		$return .= sprintf($bloc_format,$block);

		return $return;
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

} # class
