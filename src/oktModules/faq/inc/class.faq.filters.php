<?php
/**
 * @ingroup okt_module_faq
 * @brief Classe pour gérer les filtres de listes de questions.
 *
 */

use Tao\Forms\StaticFormElements as form;

class faqFilters extends filters
{
	protected $faq;

	protected $get_faq_params = array();

	protected $order_by_array = array();

	public function __construct($faq, $part='public', $params=array())
	{
		parent::__construct('faq', $faq->config, $part, $params);

		$this->faq = $faq;
	}

	public function setDefaultParams()
	{
		$this->defaults_params = array(
			'show_filters' => false,

			'page' => 1,
			'nb_per_page' => 5,
			'keyword_search'=>'',

			'active' => 2
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

	public function setQuestionsParams(&$questions_params=array())
	{
		$this->get_questions_params =& $questions_params;
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

		$this->order_by_array[__('c_c_title')] = 'title';


		# page
		$this->setFilterPage();

		# number per page
		$this->setFilterNbPerPage();

		# visibilité (seulement sur l'admin)
		$this->setFilterVisibility();

		# ordre et sens du tri
		$this->setFilterOrderBy();

		#recherche par mots clés
		$this->setFilterKeyword();
	}


	protected function setFilterVisibility()
	{
		if ($this->part !== 'admin') {
			return null;
		}

		$this->setIntFilter('active');

		$this->get_questions_params['active'] = $this->params->active;

		$this->fields['active'] = array(
			$this->form_id.'_active',
			__('c_c_action_visibility'),
			form::select(
				array('active',$this->form_id.'_active'),
				array_merge(array(__('c_c_action_all_visibility')=>2),module_faq::getQuestionsStatuses(true)),
				$this->params->active)
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

		switch ($this->params->order_by)
		{
			default:
			case 'title':
				$this->get_faq_params['order'] = 'pl.title';
			break;

			case 'categroy':
				$this->get_faq_params['order'] = 'p.categroy_id';
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

		$this->get_questions_params['order_direction'] = $this->params->order_direction;

		$this->fields['order_direction'] = array(
			$this->form_id.'_order_direction',
			__('c_c_sorting_Sort_direction'),
			form::select(
				array('order_direction', $this->form_id.'_order_direction'),
				array(__('c_c_sorting_descending')=>'DESC',__('c_c_sorting_ascending')=>'ASC'),
				$this->params->order_direction)
		);
	}

	protected function setFilterKeyword()
	{
		if (isset($_GET['keyword_search']))
		{
			$this->params->keyword_search = $_GET['keyword_search'];
			$_SESSION[$this->sess_prefix.'keyword_search'] = $this->params->keyword_search;
			$this->params->show_filters = true;
		}

		$this->get_questions_params['keyword_search'] = $this->params->keyword_search;

		$this->fields['keyword_search'] = array(
			$this->form_id.'_keyword_search',
			__('c_c_sorting_Keyword_search'),
			form::text(array('keyword_search',$this->form_id.'_keyword_search'),15,0,$this->params->keyword_search)
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

		$block .= $this->getFilter('keyword_search',$item_format);
		$block .= $this->getFilter('active',$item_format);

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
