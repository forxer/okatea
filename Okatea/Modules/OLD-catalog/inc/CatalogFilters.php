<?php
/**
 * @ingroup okt_module_catalog
 * @brief Classe pour gérer les filtres de listes de produits.
 *
 */
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Misc\BaseFilters;

class CatalogFilters extends BaseFilters
{

	protected $catalog;

	protected $get_catalog_params = array();

	protected $order_by_array = array();

	public function __construct($okt, $part = 'public', $params = array())
	{
		parent::__construct($okt, 'catalog', $okt->catalog->config, $part, $params);
		
		$this->catalog = $this->okt->catalog;
	}

	public function setDefaultParams()
	{
		$this->defaults_params = array(
			'show_filters' => false,
			
			'page' => 1,
			'nb_per_page' => 5,
			
			'visibility' => 2,
			'category_id' => 0,
			
			'promo' => 0,
			'nouvo' => 0,
			'favo' => 0,
			
			'order_by' => 'id',
			'order_direction' => 'desc'
		);
		
		parent::setDefaultParams();
	}

	public function setCatalogParams(&$catalog_params = array())
	{
		$this->get_catalog_params = & $catalog_params;
	}

	/**
	 * Récupère les filtres
	 *
	 * @param
	 *        	$part
	 * @return void
	 */
	public function getFilters()
	{
		# tableau de type de tri de base
		$this->order_by_array = array();
		$this->order_by_array['date de création'] = 'created_at';
		
		if ($this->part === 'admin')
		{
			$this->order_by_array['date de modification'] = 'updated_at';
		}
		
		$this->order_by_array['titre'] = 'title';
		
		$this->order_by_array['prix'] = 'price';
		
		# page
		$this->setFilterPage();
		
		# number per page
		$this->setFilterNbPerPage();
		
		# visibilité (seulement sur l'admin)
		$this->setFilterVisibility();
		
		# catégorie
		$this->setFilterCategory();
		
		# promotion
		$this->setFilterPromo();
		
		# nouveauté
		$this->setFilterNouvo();
		
		# favoris
		$this->setFilterFavo();
		
		# ordre et sens du tri
		$this->setFilterOrderBy();
	}

	protected function setFilterVisibility()
	{
		if ($this->part !== 'admin')
		{
			return null;
		}
		
		$this->setIntFilter('visibility');
		
		$this->get_catalog_params['visibility'] = $this->params->visibility;
		
		$this->fields['visibility'] = array(
			$this->form_id . '_visibility',
			'Visibilité',
			form::select(array(
				'visibility',
				$this->form_id . '_visibility'
			), array_merge(array(
				'toutes visibilités' => - 1
			), module_catalog::getProdsStatuses(true)), $this->params->visibility)
		);
	}

	protected function setFilterCategory()
	{
		if (! $this->config->categories_enable)
		{
			return null;
		}
		
		$this->order_by_array['catégorie'] = 'category';
		
		if (! isset($this->get_catalog_params['category_id']))
		{
			$this->setIntFilter('category_id');
			$this->get_catalog_params['category_id'] = $this->params->category_id;
		}
		
		$categories_list = $this->catalog->getCategories(array(
			'active' => 2
		));
		
		$sField = '<select id="' . $this->form_id . '_category_id" name="category_id">' . '<option value="0">toutes</option>';
		while ($categories_list->fetch())
		{
			$sField .= '<option value="' . $categories_list->id . '"' . ($categories_list->id == $this->params->category_id ? ' selected="selected"' : '') . '>' . str_repeat('&nbsp;&nbsp;', $categories_list->level) . '&bull; ' . html::escapeHTML($categories_list->name) . '</option>';
		}
		$sField .= '</select>';
		
		$this->fields['category_id'] = array(
			$this->form_id . '_category_id',
			'Catégorie',
			$sField
		);
	}

	protected function setFilterPromo()
	{
		if (! $this->config->filters[$this->part]['promo'])
		{
			return null;
		}
		
		if (! isset($this->get_catalog_params['promo']))
		{
			$this->setCheckboxFilter('promo');
			$this->get_catalog_params['promo'] = $this->params->promo;
		}
		
		$this->fields['promo'] = array(
			$this->form_id . '_promo',
			__('m_catalog_config_display_filter_promo'),
			form::checkbox(array(
				'promo',
				$this->form_id . '_promo'
			), 1, $this->get_catalog_params['promo'], $this->getActiveClass('promo'))
		);
	}

	protected function setFilterNouvo()
	{
		if (! $this->config->filters[$this->part]['nouvo'])
		{
			return null;
		}
		
		if (! isset($this->get_catalog_params['nouvo']))
		{
			$this->setCheckboxFilter('nouvo');
			$this->get_catalog_params['nouvo'] = $this->params->nouvo;
		}
		
		$this->fields['nouvo'] = array(
			$this->form_id . '_nouvo',
			__('m_catalog_config_display_filter_nouvo'),
			form::checkbox(array(
				'nouvo',
				$this->form_id . '_nouvo'
			), 1, $this->get_catalog_params['nouvo'], $this->getActiveClass('nouvo'))
		);
	}

	protected function setFilterFavo()
	{
		if (! $this->config->filters[$this->part]['favo'])
		{
			return null;
		}
		
		if (! isset($this->get_catalog_params['favo']))
		{
			$this->setCheckboxFilter('favo');
			$this->get_catalog_params['favo'] = $this->params->favo;
		}
		
		$this->fields['favo'] = array(
			$this->form_id . '_favo',
			__('m_catalog_config_display_filter_favo'),
			form::checkbox(array(
				'favo',
				$this->form_id . '_favo'
			), 1, $this->get_catalog_params['favo'], $this->getActiveClass('favo'))
		);
	}

	protected function setFilterOrderBy()
	{
		if (isset($_GET['order_direction']))
		{
			$this->params->show_filters = true;
			
			if (strtolower($_GET['order_direction']) == 'desc')
			{
				$this->params->order_direction = 'desc';
			}
			else
			{
				$this->params->order_direction = 'asc';
			}
			
			$_SESSION[$this->sess_prefix . 'order_direction'] = $this->params->order_direction;
		}
		elseif (isset($_SESSION[$this->sess_prefix . 'order_direction']))
		{
			$this->params->show_filters = true;
			$this->params->order_direction = $_SESSION[$this->sess_prefix . 'order_direction'];
		}
		
		if (isset($_GET['order_by']))
		{
			$this->params->order_by = $_GET['order_by'];
			$_SESSION[$this->sess_prefix . 'order_by'] = $this->params->order_by;
			$this->params->show_filters = true;
		}
		elseif (isset($_SESSION[$this->sess_prefix . 'order_by']))
		{
			$this->params->order_by = $_SESSION[$this->sess_prefix . 'order_by'];
			$this->params->show_filters = true;
		}
		
		$this->fields['order_by'] = array(
			$this->form_id . '_order_by',
			'Triés par',
			form::select(array(
				'order_by',
				$this->form_id . '_order_by'
			), $this->order_by_array, $this->params->order_by)
		);
		
		$this->fields['order_direction'] = array(
			$this->form_id . '_order_direction',
			'Ordre',
			form::select(array(
				'order_direction',
				$this->form_id . '_order_direction'
			), array(
				'décroissant' => 'desc',
				'croissant' => 'asc'
			), $this->params->order_direction)
		);
		
		switch ($this->params->order_by)
		{
			default:
			case 'created_at':
				$this->get_catalog_params['order'] = 'p.created_at';
				break;
			
			case 'updated_at':
				$this->get_catalog_params['order'] = 'p.updated_at';
				break;
			
			case 'title':
				$this->get_catalog_params['order'] = 'p.title';
				break;
			
			case 'category':
				$this->get_catalog_params['order'] = 'p.category_id';
				break;
			
			case 'price':
				$this->get_catalog_params['order'] = 'p.price';
				break;
		}
		
		$this->get_catalog_params['order'] .= ' ' . strtoupper($this->params->order_direction);
	}
	
	/* HTML
	------------------------------------------------*/
	
	/**
	 * Retourne le HTML des filtres
	 *
	 * @return string
	 */
	public function getFiltersFields($bloc_format = '<div class="four-cols">%s</div>', $item_format = '<p class="col field"><label for="%s">%s</label>%s</p>', $checkbox_format = '<p class="col field"><label for="%1$s">%3$s %2$s</label></p>')
	{
		$return = '';
		
		$block = '';
		
		$block .= $this->getFilter('visibility', $item_format);
		$block .= $this->getFilter('category_id', $item_format);
		
		$return .= sprintf($bloc_format, $block);
		
		$block = '';
		
		$block .= $this->getFilter('promo', $checkbox_format);
		$block .= $this->getFilter('nouvo', $checkbox_format);
		$block .= $this->getFilter('favo', $checkbox_format);
		
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
