<?php
/**
 * Extension de la classe filters pour l'administration.
 *
 * @addtogroup Okatea
 *
 */

class logAdminFilters extends filters
{

	protected $logAdmin;
	protected $aLogParams = array();
	protected $order_by_array = array();
	protected $type_array = array();
	protected $action_array = array();

	public function __construct($logAdmin)
	{
		$oConfig = new arrayObject();
		$oConfig->admin_default_nb_per_page = 30;
		$oConfig->admin_default_order_by = 'date';
		$oConfig->admin_default_order_direction = 'DESC';
		$oConfig->admin_filters_style = 'dialog';

		parent::__construct('logAdmin', $oConfig, 'admin');
		$this->logAdmin = $logAdmin;
		$this->logAdmin->oConfig = $oConfig;
	}

	public function setDefaultParams()
	{
		$this->defaults_params = array(
			'date_min' => '',
			'date_max' => '',
			'type' => 1,
			'code' => 1,
			'order_by' => $this->config->admin_default_order_by,
			'order_direction' => $this->config->admin_default_order_direction
		);

		parent::setDefaultParams();
	}

	public function setLogsParams(&$logs_params=array())
	{
		$this->aLogParams =& $logs_params;
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
		$this->order_by_array = array(
			'date' => 'date',
			'type' => 'type',
			'action' => 'code',
			'IP' => 'ip'
		);

		# dates
		$this->setFilterDates();

		# types
		$this->setFilterType();

		# action
		$this->setFilterAction();

		# page
		$this->setFilterPage();

		# number per page
		$this->setFilterNbPerPage();

		# ordre et sens du tri
		$this->setFilterOrderBy();
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

		switch ($this->params->order_by)
		{
			default:
			case 'type':
				$this->aLogParams['order'] = 'type';
			break;

			case 'code':
				$this->aLogParams['order'] = 'code';
			break;

			case 'date':
				$this->aLogParams['order'] = 'date';
			break;

			case 'ip':
				$this->aLogParams['order'] = 'ip';
			break;
		}

		$this->fields['order_by'] = array(
			$this->form_id.'_order_by',
			__('c_c_sorting_Sorted_by'),
			form::select(
				array('order_by', $this->form_id.'_order_by'),
				$this->order_by_array,
				$this->params->order_by)
		);

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

		$this->aLogParams['order_direction'] = $this->params->order_direction;

		$this->fields['order_direction'] = array(
			$this->form_id.'_order_direction',
			__('c_c_sorting_Sort_direction'),
			form::select(
				array('order_direction', $this->form_id.'_order_direction'),
				array(__('c_c_sorting_descending')=>'DESC',__('c_c_sorting_ascending')=>'ASC'),
				$this->params->order_direction)
		);
	}

	protected function setFilterDates()
	{
		if (isset($_GET['date_min']))
		{
			$this->params->date_min = $_GET['date_min'];
			$_SESSION[$this->sess_prefix.'date_min'] = $this->params->date_min;
			$this->params->show_filters = true;
		}

		$this->aLogParams['date_min'] = $this->params->date_min;

		$this->fields['date_min'] = array(
			$this->form_id.'date_min',
			__('c_a_config_logadmin_Date_min'),
			form::text(array('date_min',$this->form_id.'_date_min'),15,0,$this->params->date_min, 'datepicker')
		);

		if (isset($_GET['date_max']))
		{
			$this->params->date_max = $_GET['date_max'];
			$_SESSION[$this->sess_prefix.'date_max'] = $this->params->date_max;
			$this->params->show_filters = true;
		}

		$this->aLogParams['date_max'] = $this->params->date_max;

		$this->fields['date_max'] = array(
			$this->form_id.'date_max',
			__('c_a_config_logadmin_Date_max'),
			form::text(array('date_max',$this->form_id.'_date_max'),15,0,$this->params->date_max, 'datepicker')
		);
	}

	protected function setFilterType()
	{
		# tableau de tri par type
		$this->type_array = array_merge(array('tous les types' => '1'), array_flip($this->logAdmin->getTypes()));

		if (!isset($this->aLogParams['type']))
		{
			$this->setIntFilter('type');
			$this->aLogParams['type'] = $this->params->type;
		}

		$this->fields['type'] = array(
			$this->form_id.'_type',
			__('c_a_config_logadmin_type'),
			form::select(
				array('type', $this->form_id.'_type'),
				$this->type_array,
				$this->params->type
			)
		);
	}

	protected function setFilterAction()
	{
		# tableau de tri par action
		$this->action_array = array_merge(array('toutes les actions' => '1'), array_flip($this->logAdmin->getCodes()));

		if (!isset($this->aLogParams['code']))
		{
			$this->setIntFilter('code');
			$this->aLogParams['code'] = $this->params->code;
		}

		$this->fields['code'] = array(
			$this->form_id.'_code',
			__('c_a_config_logadmin_code'),
			form::select(
				array('code', $this->form_id.'_code'),
				$this->action_array,
				$this->params->code)
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
		$return = '';

		$block = '';

		$block .= $this->getFilter('date_min',$item_format);
		$block .= $this->getFilter('date_max',$item_format);

		$return .= sprintf($bloc_format,$block);

		$block = '';

		$block .= $this->getFilter('type',$item_format);
		$block .= $this->getFilter('code',$item_format);

		$return .= sprintf($bloc_format,$block);

		$block = '';

		$block .= $this->getFilter('order_by',$item_format);
		$block .= $this->getFilter('order_direction',$item_format);
		$block .= $this->getFilter('nb_per_page',$item_format);

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
