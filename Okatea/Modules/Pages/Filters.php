<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Pages;

use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Html\Escaper;
use Okatea\Tao\Misc\BaseFilters;

class Filters extends BaseFilters
{
	protected $pages;

	protected $get_pages_params = array();

	protected $order_by_array = array();

	public function __construct($okt, $part='public', $params=array())
	{
		parent::__construct($okt, 'pages', $okt->Pages->config, $part, $params);

		$this->pages = $this->okt->Pages;
	}

	public function setDefaultParams()
	{
		$this->defaults_params = array(
			'show_filters' => false,

			'page' => 1,
			'nb_per_page' => 5,

			'active' => 2,
			'category_id' => 0
		);

		if ($this->part === 'admin') {
			$this->defaults_params['language'] = $this->okt->user->language;
			$this->defaults_params['order_by'] = $this->config->admin_default_order_by;
			$this->defaults_params['order_direction'] = $this->config->admin_default_order_direction;
		}
		else {
			$this->defaults_params['order_by'] = $this->config->public_default_order_by;
			$this->defaults_params['order_direction'] = $this->config->public_default_order_direction;
		}

		parent::setDefaultParams();
	}

	public function setPagesParams(&$pages_params=array())
	{
		$this->get_pages_params =& $pages_params;
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
		$this->order_by_array[__('m_pages_filters_created')] = 'created_at';

		if ($this->part === 'admin') {
			$this->order_by_array[__('m_pages_filters_updated')] = 'updated_at';
		}

		$this->order_by_array[__('m_pages_filters_title')] = 'title';


		# page
		$this->setFilterPage();

		# number per page
		$this->setFilterNbPerPage();

		# visibilité (seulement sur l'admin)
		$this->setFilterVisibility();

		# rubrique
		$this->setFilterCategory();

		# langue
		$this->setFilterLanguage();

		# ordre et sens du tri
		$this->setFilterOrderBy();
	}

	protected function setFilterVisibility()
	{
		if ($this->part !== 'admin') {
			return null;
		}

		if (!isset($this->get_pages_params['active']))
		{
			$this->setIntFilter('active');
			$this->get_pages_params['active'] = $this->params->active;
		}

		$this->fields['active'] = array(
			$this->form_id.'_active',
			__('m_pages_filters_visibility'),
			form::select(
				array('active',$this->form_id.'_active'),
				array(__('c_c_All_f')=>2, __('c_c_action_Visibles')=>1, __('c_c_action_Hiddens_fem')=>0),
				$this->get_pages_params['active'],
				$this->getActiveClass('active')
			)
		);
	}

	protected function setFilterCategory()
	{
		if (!$this->config->categories['enable']) {
			return null;
		}

		$this->order_by_array[__('m_pages_filters_category')] = 'rubriques';

		if (!isset($this->get_pages_params['category_id']))
		{
			$this->setIntFilter('category_id');
			$this->get_pages_params['category_id'] = $this->params->category_id;
		}

		$rubriques_list = $this->pages->categories->getCategories(array(
			'active' => 2,
			'language' => $this->okt->user->language
		));


		$sField =
		'<select id="'.$this->form_id.'_category_id" name="category_id" class="select '.$this->getActiveClass('category_id').'">'.
			'<option value="0">'.__('c_c_All_f').'</option>';
			while ($rubriques_list->fetch()) {
				$sField .= '<option value="'.$rubriques_list->id.'"'.($rubriques_list->id == $this->params->category_id ? ' selected="selected"' : '').'>'.str_repeat('&nbsp;&nbsp;&nbsp;',$rubriques_list->level).'&bull; '.Escaper::html($rubriques_list->title).'</option>';
			}
		$sField .= '</select>';


		$this->fields['category_id'] = array(
			$this->form_id.'category_id',
			__('m_pages_filters_categories'),
			$sField
		);
	}

	protected function setFilterLanguage()
	{
		if ($this->part !== 'admin' || $this->okt->languages->unique) {
			return null;
		}

		if (!isset($this->get_pages_params['language']))
		{
			$this->setFilter('language');
			$this->get_pages_params['language'] = $this->params->language;
		}

		$aSelectLanguagesValues = array();
		foreach ($this->okt->languages->list as $aLanguage) {
			$aSelectLanguagesValues[Escaper::html($aLanguage['title'])] = Escaper::html($aLanguage['code']);
		}

		$this->fields['language'] = array(
			$this->form_id.'language',
			__('m_pages_filters_language'),
			form::select(
				array('language',$this->form_id.'_language'),
				$aSelectLanguagesValues,
				$this->get_pages_params['language'],
				$this->getActiveClass('language')
			)
		);
	}

	protected function setFilterOrderBy()
	{
		# ordre du tri
		$this->setFilter('order_by');

		$this->fields['order_by'] = array(
			$this->form_id.'_order_by',
			__('c_c_sorting_Sorted_by_f'),
			form::select(
				array('order_by', $this->form_id.'_order_by'),
				$this->order_by_array,
				$this->params->order_by,
				$this->getActiveClass('order_by')
			)
		);

		switch ($this->params->order_by)
		{
			default:
			case 'created_at':
				$this->get_pages_params['order'] = 'p.created_at';
			break;

			case 'updated_at':
				$this->get_pages_params['order'] = 'p.updated_at';
			break;

			case 'title':
				$this->get_pages_params['order'] = 'pl.title';
			break;

			case 'rubrique':
				$this->get_pages_params['order'] = 'p.category_id';
			break;
		}

		# sens du tri
		$this->setFilter('order_direction');

		$this->get_pages_params['order_direction'] = $this->params->order_direction;

		$this->fields['order_direction'] = array(
			$this->form_id.'_order_direction',
			__('c_c_sorting_Sort_direction'),
			form::select(
				array('order_direction', $this->form_id.'_order_direction'),
				array(__('c_c_sorting_Descending')=>'DESC',__('c_c_sorting_Ascending')=>'ASC'),
				$this->params->order_direction,
				$this->getActiveClass('order_direction')
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
		$return = '';

		$block = '';

		$block .= $this->getFilter('active', $item_format);
		$block .= $this->getFilter('category_id', $item_format);
		$block .= $this->getFilter('language', $item_format);

		$return .= sprintf($bloc_format, $block);

		$block = '';

		$block .= $this->getFilter('order_by', $item_format);
		$block .= $this->getFilter('order_direction', $item_format);
		$block .= $this->getFilter('nb_per_page', $item_format);

		$return .= sprintf($bloc_format, $block);

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

}
