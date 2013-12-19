<?php
/**
 * @ingroup okt_module_diary
 * @brief
 *
 */

use Tao\Misc\BaseFilters;
use Tao\Forms\Statics\FormElements as form;

class DiaryFilters extends BaseFilters
{
	protected $get_events_params = array();

	protected $order_by_array = array();

	public function __construct($okt, $oConfig, $part='public', $params=array())
	{
		parent::__construct($okt, 'diary', $oConfig, $part, $params);
	}

	public function setDefaultParams()
	{
		$this->defaults_params = array(
			'show_filters' => false,

			'page' => 1,
			'nb_per_page' => 5,

			'year' => false,
			'month' => false,

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
		$this->get_events_params =& $params;
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

		if (!isset($this->get_events_params['visibility']))
		{
			$this->setIntFilter('visibility');
			$this->get_events_params['visibility'] = $this->params->visibility;
		}

		$this->fields['visibility'] = array(
			$this->form_id.'_visibility',
			'Visibilité',
			form::select(
				array('visibility', $this->form_id.'_visibility'),
				array('toutes visibilités'=>2, 'visibles'=>1, 'masqués'=>0),
				$this->get_events_params['visibility']
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
				$this->get_events_params['order'] = 'p.created_at';
			break;

			case 'updated_at':
				$this->get_events_params['order'] = 'p.updated_at';
			break;

			case 'title':
				$this->get_events_params['order'] = 'p.title';
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

		$this->get_events_params['order_direction'] = $this->params->order_direction;

		$this->fields['order_direction'] = array(
			$this->form_id.'_order_direction',
			__('c_c_sorting_Sort_direction'),
			form::select(
				array('order_direction', $this->form_id.'_order_direction'),
				array(__('c_c_sorting_descending')=>'DESC',__('c_c_sorting_ascending')=>'ASC'),
				$this->params->order_direction)
		);
	}

	/**
	 * Récupère les filtres de date
	 *
	 * @param $part
	 * @return void
	 */
	public function getFiltersDate()
	{
		# année
		$this->setFilterYear();

		# mois
		$this->setFilterMonth();
	}


	protected function setFilterYear()
	{
		if (isset($this->config->filters) && !$this->config->filters[$this->part]['year']) {
			return null;
		}

		$year = date('Y');
		$aYear = array();

		for($i=-2; $i<6; $i++){
			$aYear[$year+$i] = $year+$i;
		}

		$this->setIntFilter('year');

		$this->fields['year'] = array(
				$this->form_id.'_year',
				__('m_diary_filters_year'),
				form::select(
					array('year', $this->form_id.'_year'),
					$aYear,
					$this->params->year)
		);
	}


	protected function setFilterMonth()
	{

		if (isset($this->config->filters) && !$this->config->filters[$this->part]['month']) {
			return null;
		}

		$aMonth = array(
				"Janvier" => "01",
				"Février" => "02",
				"Mars" => "03",
				"Avril" => "04",
				"Mai" => "05",
				"Juin" => "06",
				"Juillet" => "07",
				"Août" => "08",
				"Septembre" => "09",
				"Octobre" => "10",
				"Novembre" => "11",
				"Décembre" => "12"
		);

		$this->setIntFilter('month');

		$this->fields['month'] = array(
				$this->form_id.'_month',
				__('m_diary_filters_month'),
				form::select(
						array('month', $this->form_id.'_month'),
						$aMonth,
						$this->params->month)
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

		$block .= $this->getFilter('order_by', $item_format);
		$block .= $this->getFilter('order_direction', $item_format);

		$return .= sprintf($bloc_format,$block);

		return $return;
	}

	/**
	 * Retourne le HTML des filtres de date
	 *
	 * @return string
	 */
	public function getFiltersFieldsDate(
			$bloc_format='<div class="two-cols">%s</div>',
			$item_format='<p class="col field"><label for="%s">%s</label>%s</p>')
	{
		$return = '';

		$block = '';

		$block .= $this->getFilter('year', $item_format);
		$block .= $this->getFilter('month', $item_format);

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
	public function getFilter($id, $item_format='<p class="col field"><label for="%s">%s</label>%s</p>')
	{
		if (isset($this->fields[$id])) {
			return sprintf($item_format, $this->fields[$id][0], $this->fields[$id][1], $this->fields[$id][2]);
		}
	}

}
