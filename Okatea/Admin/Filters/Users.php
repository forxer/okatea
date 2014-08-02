<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Admin\Filters;

use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Html\Escaper;
use Okatea\Tao\Misc\BaseFilters;
use Okatea\Tao\Users\Groups;

class Users extends BaseFilters
{

	protected $get_users_params = array();

	protected $order_by_array = array();

	public function __construct($okt, $part = 'public', $params = array())
	{
		parent::__construct($okt, 'users', $okt['config']->users_filters, $part, $params);
		
		$this->order_by_array = array();
	}

	public function setDefaultParams()
	{
		$this->defaults_params = array(
			'show_filters' => false,
			
			'page' => 1,
			'nb_per_page' => 5,
			
			'status' => 2,
			
			'group_id' => - 1,
			
			'order_by' => 'registration_date',
			'order_direction' => 'desc'
		);
		
		parent::setDefaultParams();
	}

	public function setUsersParams(&$users_params = array())
	{
		$this->get_users_params = & $users_params;
	}

	/**
	 * Créer les filtres
	 *
	 * @return void
	 */
	public function getFilters()
	{
		# tableau de type de tri de base
		$this->order_by_array[__('c_a_users_registration_date')] = 'registration_date';
		$this->order_by_array[__('c_c_user_Username')] = 'username';
		$this->order_by_array[__('c_c_Group')] = 'group_id';
		
		# status (seulement sur l'admin)
		$this->setFilterActive();
		
		# page
		$this->setFilterPage();
		
		# number per page
		$this->setFilterNbPerPage();
		
		# groupe
		$this->setFilterGroup();
		
		# ordre et sens du tri
		$this->setFilterOrderBy();
	}

	protected function setFilterActive()
	{
		if ($this->part !== 'admin')
		{
			return null;
		}
		
		if (! isset($this->get_users_params['status']))
		{
			$this->setIntFilter('status');
			$this->get_users_params['status'] = $this->params->status;
		}
		
		$this->fields['status'] = array(
			$this->form_id . '_status',
			__('c_a_users_filters_status'),
			form::select(array(
				'status',
				$this->form_id . '_status'
			), array(
				__('c_c_All') => 2,
				__('c_c_Enabled') => 1,
				__('c_c_Disabled') => 0
			), $this->get_users_params['status'], $this->getActiveClass('status'))
		);
	}

	protected function setFilterGroup()
	{
		$this->setIntFilter('group_id');
		
		if ($this->params->group_id != - 1)
		{
			$this->get_users_params['group_id'] = $this->params->group_id;
		}
		
		$rsGroups = $this->okt->getGroups()->getGroups(array(
			'language' => $this->okt->user->language
		));
		
		$groups_array = array(
			__('c_c_All') => - 1,
			__('c_a_users_wait_of_validation') => Groups::UNVERIFIED
		);
		while ($rsGroups->fetch())
		{
			if ($rsGroups->group_id == Groups::GUEST || $rsGroups->group_id == Groups::ADMIN && ! $this->okt->user->is_admin || $rsGroups->group_id == Groups::SUPERADMIN && ! $this->okt->user->is_superadmin)
			{
				continue;
			}
			
			$groups_array[Escaper::html($rsGroups->title)] = $rsGroups->group_id;
		}
		
		$this->fields['group_id'] = array(
			$this->form_id . '_group_id',
			__('c_c_Group'),
			form::select(array(
				'group_id',
				$this->form_id . '_group_id'
			), $groups_array, $this->params->group_id, $this->getActiveClass('group_id'))
		);
	}

	protected function setFilterOrderBy()
	{
		if ($this->request->query->has('order_direction'))
		{
			$this->params->show_filters = true;
			
			if (strtolower($this->request->query->get('order_direction')) === 'desc')
			{
				$this->params->order_direction = 'desc';
			}
			else
			{
				$this->params->order_direction = 'asc';
			}
			
			$this->okt['session']->set($this->sess_prefix . 'order_direction', $this->params->order_direction);
			$this->setActiveFilter('order_direction');
		}
		elseif ($this->okt['session']->has($this->sess_prefix . 'order_direction'))
		{
			$this->params->show_filters = true;
			$this->params->order_direction = $this->okt['session']->get($this->sess_prefix . 'order_direction');
			$this->setActiveFilter('order_direction');
		}
		
		if ($this->request->query->has('order_by'))
		{
			$this->params->order_by = $this->request->query->get('order_by');
			$this->okt['session']->set($this->sess_prefix . 'order_by', $this->params->order_by);
			$this->params->show_filters = true;
			$this->setActiveFilter('order_by');
		}
		elseif ($this->okt['session']->has($this->sess_prefix . 'order_by'))
		{
			$this->params->order_by = $this->okt['session']->get($this->sess_prefix . 'order_by');
			$this->params->show_filters = true;
			$this->setActiveFilter('order_by');
		}
		
		$this->fields['order_by'] = array(
			$this->form_id . '_order_by',
			__('c_c_sorting_Sorted_by'),
			form::select(array(
				'order_by',
				$this->form_id . '_order_by'
			), $this->order_by_array, $this->params->order_by, $this->getActiveClass('order_by'))
		);
		
		$this->fields['order_direction'] = array(
			$this->form_id . '_order_direction',
			__('c_c_sorting_Sort_direction'),
			form::select(array(
				'order_direction',
				$this->form_id . '_order_direction'
			), array(
				__('c_c_sorting_Descending') => 'desc',
				__('c_c_sorting_Ascending') => 'asc'
			), $this->params->order_direction, $this->getActiveClass('order_direction'))
		);
		
		switch ($this->params->order_by)
		{
			default:
			case 'registration_date':
				$this->get_users_params['order'] = 'u.registered';
				break;
			
			case 'username':
				$this->get_users_params['order'] = 'u.username';
				break;
			
			case 'group_id':
				$this->get_users_params['order'] = 'u.group_id';
				break;
		}
		
		$this->get_users_params['order'] .= ' ' . strtoupper($this->params->order_direction);
	}
	
	/* HTML
	------------------------------------------------*/
	
	/**
	 * Retourne le HTML des filtres
	 *
	 * @return string
	 */
	public function getFiltersFields($bloc_format = '<div class="four-cols">%s</div>', $item_format = '<p class="col field"><label for="%s">%s</label>%s</p>')
	{
		$return = '';
		
		$block = '';
		$block .= $this->getFilter('status', $item_format);
		$block .= $this->getFilter('group_id', $item_format);
		
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
	public function getFilter($id, $item_format = '<p class="col field"><label for="%s">%s</label>%s</p>')
	{
		if (isset($this->fields[$id]))
		{
			return sprintf($item_format, $this->fields[$id][0], $this->fields[$id][1], $this->fields[$id][2]);
		}
	}
}
