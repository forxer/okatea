<?php
/**
 * @ingroup okt_module_estimate
 * @brief Classe pour gérer les filtres de listes de demandes de devis.
 *
 */

use Tao\Misc\FiltersBase;
use Tao\Forms\Statics\FormElements as form;

class estimateFilters extends FiltersBase
{
	protected $estimate;

	protected $get_estimates_params = array();

	protected $order_by_array = array();

	public function __construct($okt, $part='public', $params=array())
	{
		parent::__construct($okt, 'estimate', $okt->estimate->config, $part, $params);

		$this->estimate = $this->okt->estimate;
	}

	public function setDefaultParams()
	{
		$this->defaults_params = array(
			'show_filters' => false,

			'page' => 1,
			'nb_per_page' => 5,

			'status' => 2,

			'order_by' => 'id',
			'order_direction' => 'desc'
		);

		parent::setDefaultParams();
	}

	public function setEstimatesParams(&$params=array())
	{
		$this->get_estimates_params =& $params;

	}

	/**
	 * Récupère les filtres.
	 *
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

		# statut
		$this->setFilterStatus();

		# ordre et sens du tri
		$this->setFilterOrderBy();
	}


	protected function setFilterStatus()
	{
		$this->setIntFilter('status');

		$this->get_estimates_params['status'] = $this->params->status;

		$this->fields['status'] = array(
			$this->form_id.'_status',
			__('m_estimate_filters_status'),
			form::select(
				array('status',$this->form_id.'_status'),
				array_merge(array('&nbsp;'=>2),module_estimate::getEstimatesStatuses(true)),
				$this->params->status)
		);
	}

	protected function setFilterOrderBy()
	{
		if (isset($_GET['order_direction']))
		{
			$this->params->show_filters = true;

			if (strtolower($_GET['order_direction']) == 'desc') {
				$this->params->order_direction = 'desc';
			}
			else {
				$this->params->order_direction = 'asc';
			}

			$_SESSION[$this->sess_prefix.'order_direction'] = $this->params->order_direction;
		}
		elseif (isset($_SESSION[$this->sess_prefix.'order_direction']))
		{
			$this->params->show_filters = true;
			$this->params->order_direction = $_SESSION[$this->sess_prefix.'order_direction'];
		}

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
			'Triés par',
			form::select(
				array('order_by',$this->form_id.'_order_by'),
				$this->order_by_array,
				$this->params->order_by)
		);

		$this->fields['order_direction'] = array(
			$this->form_id.'_order_direction',
			'Ordre',
			form::select(
				array('order_direction',$this->form_id.'_order_direction'),
				array('décroissant'=>'desc','croissant'=>'asc'),
				$this->params->order_direction)
		);

		switch ($this->params->order_by)
		{
			default:
			case 'id':
				$this->get_estimates_params['order'] = 'e.id';
			break;

			case 'created_at':
				$this->get_estimates_params['order'] = 'e.created_at';
			break;

			case 'updated_at':
				$this->get_estimates_params['order'] = 'e.updated_at';
			break;
		}

		$this->get_estimates_params['order'] .= ' '.strtoupper($this->params->order_direction);
	}


	/* HTML
	------------------------------------------------*/

	/**
	 * Retourne le HTML des filtres.
	 *
	 * @return string
	 */
	public function getFiltersFields(
		$bloc_format='<div class="four-cols">%s</div>',
		$item_format='<p class="col field"><label for="%s">%s</label>%s</p>',
		$checkbox_format = '<p class="col field"><label for="%1$s">%3$s %2$s</label></p>')
	{
		$return = '';

		$block = '';

		$block .= $this->getFilter('status',$item_format);

		$return .= sprintf($bloc_format,$block);

		$block = '';

		$block .= $this->getFilter('order_by',$item_format);
		$block .= $this->getFilter('order_direction',$item_format);
		$block .= $this->getFilter('nb_per_page',$item_format);

		$return .= sprintf($bloc_format,$block);

		return $return;
	}

	/**
	 * Retourne le HTML d'un filtre.
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
