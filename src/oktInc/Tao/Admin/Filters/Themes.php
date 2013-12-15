<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Admin\Filters;

use Tao\Misc\FiltersBase;

/**
 * Extension de la classe filters pour l'administration des thèmes.
 *
 */
class Themes extends FiltersBase
{
	protected $get_posts_params = array();

	protected $order_by_array = array();

	public function __construct($okt, $params=array())
	{
		$oConfig = new \ArrayObject();
		$oConfig->admin_default_order_by = 'name';
		$oConfig->admin_default_nb_per_page = 16;
		$oConfig->admin_default_order_direction = 'ASC';

		parent::__construct($okt, 'themes', $oConfig, 'admin', $params);
	}

	public function setDefaultParams()
	{
		$this->defaults_params = array(
			'show_filters' => false,

			'page' => 1,
			'nb_per_page' => 5,

			'admin_default_order_by' => 'name',
			'admin_default_nb_per_page' => 16,
			'admin_default_order_direction' => 'ASC'
		);

		$this->defaults_params['order_by'] = $this->config->admin_default_order_by;
		$this->defaults_params['order_direction'] = $this->config->admin_default_order_direction;

		parent::setDefaultParams();
	}

	public function setPostsParams(&$aPostsParams=array())
	{
		$this->get_posts_params =& $aPostsParams;
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
		/*
		$this->order_by_array = array();
		$this->order_by_array[__('m_news_filters_created')] = 'created_at';

		if ($this->part === 'admin') {
			$this->order_by_array[__('m_news_filters_updated')] = 'updated_at';
		}

		$this->order_by_array[__('m_news_filters_title')] = 'title';
		*/


		# page
		$this->setFilterPage();

		# number per page
		$this->setFilterNbPerPage();

		# ordre et sens du tri
	//	$this->setFilterOrderBy();
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

		$block .= $this->getFilter('order_by',$item_format);
		$block .= $this->getFilter('order_direction',$item_format);
		$block .= $this->getFilter('nb_per_page',$item_format);

		$return .= sprintf($bloc_format,$block);

		return $return;
	}
}
