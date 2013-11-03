<?php
/**
 * @ingroup okt_module_estimate
 * @brief La classe principale du module.
 *
 */

class module_estimate extends oktModule
{
	public $config = null;

	protected $t_estimate;
	protected $t_products;
	protected $t_accessories;
	protected $t_estimate_product;
	protected $t_estimate_product_accessories;
	protected $t_users;
	protected $locales = null;

	protected function prepend()
	{
		global $oktAutoloadPaths;

		# chargement des principales locales
		l10n::set(__DIR__.'/locales/'.$this->okt->user->language.'/main');

		# autoload
		$oktAutoloadPaths['estimateController'] = __DIR__.'/inc/class.estimate.controller.php';
		$oktAutoloadPaths['estimateProducts'] = __DIR__.'/inc/class.estimate.products.php';
		$oktAutoloadPaths['estimateAccessories'] = __DIR__.'/inc/class.estimate.accessories.php';

		# permissions
		$this->okt->addPermGroup('estimate', __('m_estimate_perm_group'));
			$this->okt->addPerm('estimate', __('m_estimate_perm_global'), 'estimate');
			$this->okt->addPerm('estimate_products', __('m_estimate_perm_products'), 'estimate');
			$this->okt->addPerm('estimate_accessories', __('m_estimate_perm_accessories'), 'estimate');
			$this->okt->addPerm('estimate_config', 'Configuration', 'estimate');

		# les tables
		$this->t_estimate = $this->db->prefix.'mod_estimate';
		$this->t_products = $this->db->prefix.'mod_estimate_products';
		$this->t_accessories = $this->db->prefix.'mod_estimate_accessories';
		$this->t_estimate_product = $this->db->prefix.'mod_estimate_product';
		$this->t_estimate_product_accessories = $this->db->prefix.'mod_estimate_product_accessories';
		$this->t_users = $this->db->prefix.'core_users';

		# config
		$this->config = $this->okt->newConfig('conf_estimate');
		$this->config->url = $this->okt->page->getBaseUrl().$this->config->public_estimate_url[$this->okt->user->language];

		# définition des routes
		$this->okt->router->addRoute('estimatePage', new oktRoute(
			'^('.html::escapeHTML(implode('|',$this->config->public_estimate_url)).')$',
			'estimateController', 'estimatePage'
		));

		$this->products = new estimateProducts($this->okt, $this->t_products, $this->t_accessories);
		$this->accessories = new estimateAccessories($this->okt, $this->t_accessories, $this->t_products);
	}

	protected function prepend_admin()
	{
		# chargement des locales admin
		l10n::set(__DIR__.'/locales/'.$this->okt->user->language.'/admin');

		# on détermine si on est actuellement sur ce module
		$this->onThisModule();

		# on ajoutent un item au menu admin
		if (!defined('OKT_DISABLE_MENU'))
		{
			$this->okt->page->estimateSubMenu = new htmlBlockList(null,adminPage::$formatHtmlSubMenu);
			$this->okt->page->mainMenu->add(
				__('m_estimate_menu_Estimates'),
				'module.php?m=estimate',
				ON_ESTIMATE_MODULE,
				30,
				$this->okt->checkPerm('estimate'),
				null,
				$this->okt->page->estimateSubMenu,
				$this->url().'/icon.png'
			);
				$this->okt->page->estimateSubMenu->add(
					__('m_estimate_menu_Estimates_list'),
					'module.php?m=estimate&amp;action=index',
					ON_ESTIMATE_MODULE && (!$this->okt->page->action || $this->okt->page->action === 'index'),
					1
				);
				$this->okt->page->estimateSubMenu->add(
					__('m_estimate_menu_Products'),
					'module.php?m=estimate&amp;action=products',
					ON_ESTIMATE_MODULE && ($this->okt->page->action === 'products' || $this->okt->page->action === 'product'),
					2,
					$this->okt->checkPerm('estimate_products')
				);
				$this->okt->page->estimateSubMenu->add(
					__('m_estimate_menu_Accessories'),
					'module.php?m=estimate&amp;action=accessories',
					ON_ESTIMATE_MODULE && ($this->okt->page->action === 'accessories' || $this->okt->page->action === 'accessory'),
					3,
					$this->okt->checkPerm('estimate_accessories')
				);
				$this->okt->page->estimateSubMenu->add(
					__('c_a_menu_configuration'),
					'module.php?m=estimate&amp;action=config',
					ON_ESTIMATE_MODULE && ($this->okt->page->action === 'config'),
					10,
					$this->okt->checkPerm('estimate_config')
				);
		}
	}


	/* Gestion des devis
	----------------------------------------------------------*/

	/**
	 * Retourne une liste de devis
	 *
	 * @param array $params
	 * @param boolean $count_only
	 * @return object recordset/integer
	 */
	public function getEstimates($params=array(), $count_only=false)
	{
		$reqPlus = '';

		if (!empty($params['id'])) {
			$reqPlus .= ' AND d.id='.(integer)$params['id'].' ';
		}

		if (!empty($aParams['user_id'])) {
			$sReqPlus .= ' AND d.user_id='.(integer)$aParams['user_id'].' ';
		}

		if ($count_only)
		{
			$query =
			'SELECT COUNT(d.id) AS num_estimate '.
			'FROM '.$this->t_estimate.' AS d '.
				'LEFT JOIN '.$this->t_users.' AS u ON u.id=d.user_id '.
			'WHERE 1 '.
			$reqPlus;
		}
		else {
			$query =
			'SELECT d.id, d.status, d.start_at, d.end_at, d.comment, '.
			'd.user_id, d.tel, d.address '.
			'u.username, u.lastname, u.firstname, u.email '.
			'FROM '.$this->t_estimate.' AS d '.
				'LEFT JOIN '.$this->t_users.' AS u ON u.id=d.user_id '.
			'WHERE 1 '.
			$reqPlus;

			if (!empty($params['order'])) {
				$query .= 'ORDER BY '.$params['order'].' ';
			}
			else {
				$query .= 'ORDER BY d.id DESC ';
			}

			if (!empty($params['limit'])) {
				$query .= 'LIMIT '.$params['limit'].' ';
			}
		}

		if (($rs = $this->db->select($query)) === false)
		{
			if ($count_only) {
				return 0;
			}
			else {
				return new recordset(array());
			}
		}

		if ($count_only) {
			return (integer)$rs->num_estimate;
		}
		else {
			return $rs;
		}
	}


} # class
