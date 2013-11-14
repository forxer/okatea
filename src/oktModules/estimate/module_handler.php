<?php
/**
 * @ingroup okt_module_estimate
 * @brief La classe principale du module.
 *
 */

class module_estimate extends oktModule
{
	public $config = null;
	public $filters = null;

	protected $t_estimate;
	protected $t_products;
	protected $t_accessories;
	protected $t_users;

	protected function prepend()
	{
		global $oktAutoloadPaths;

		# chargement des principales locales
		l10n::set(__DIR__.'/locales/'.$this->okt->user->language.'/main');

		# autoload
		$oktAutoloadPaths['estimateController'] = __DIR__.'/inc/class.estimate.controller.php';
		$oktAutoloadPaths['estimateFilters'] = __DIR__.'/inc/class.estimate.filters.php';
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
		$this->t_users = $this->db->prefix.'core_users';

		# config
		$this->config = $this->okt->newConfig('conf_estimate');
		$this->config->url = $this->okt->page->getBaseUrl().$this->config->public_form_url[$this->okt->user->language];

		# définition des routes
		$this->okt->router->addRoute('estimateForm', new oktRoute(
			'^('.html::escapeHTML(implode('|',$this->config->public_form_url)).')$',
			'estimateController', 'estimateForm'
		));
		$this->okt->router->addRoute('estimateSummary', new oktRoute(
			'^('.html::escapeHTML(implode('|',$this->config->public_summary_url)).')$',
			'estimateController', 'estimateSummary'
		));

		$this->products = new estimateProducts($this->okt, $this->t_products, $this->t_accessories);

		if ($this->config->enable_accessories) {
			$this->accessories = new estimateAccessories($this->okt, $this->t_accessories, $this->t_products);
		}
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
					$this->config->enable_accessories && $this->okt->checkPerm('estimate_accessories')
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

	/**
	 * Initialisation des filtres.
	 *
	 * @param string $sPart 	'public' ou 'admin'
	 */
	public function filtersStart($sPart='public')
	{
		if ($this->filters === null || !($this->filters instanceof estimateFilters)) {
			$this->filters = new estimateFilters($this->okt, $sPart);
		}
	}


	/* Gestion des demandes de devis
	----------------------------------------------------------*/

	/**
	 * Retourne une liste de demande de devis sous forme de recordset.
	 *
	 * @param array $aParams
	 * @param boolean $bCountOnly
	 * @return object recordset/integer
	 */
	public function getEstimates(array $aParams=array(), $bCountOnly=false)
	{
		$reqPlus = '';

		if (!empty($aParams['id'])) {
			$reqPlus .= ' AND e.id='.(integer)$aParams['id'].' ';
		}

		if (!empty($aParams['user_id'])) {
			$sReqPlus .= ' AND e.user_id='.(integer)$aParams['user_id'].' ';
		}

		if (isset($aParams['status'])) {
			$sReqPlus .= ' AND e.status='.(integer)$aParams['status'].' ';
		}

		if ($bCountOnly)
		{
			$query =
			'SELECT COUNT(e.id) AS num_estimate '.
			'FROM '.$this->t_estimate.' AS e '.
				'LEFT JOIN '.$this->t_users.' AS u ON u.id=e.user_id '.
			'WHERE 1 '.
			$reqPlus;
		}
		else
		{
			$query =
			'SELECT e.id, e.status, e.start_at, e.end_at, '.
			'e.user_id, e.content, e.created_at, e.updated_at, '.
			'u.username, u.lastname, u.firstname, u.email '.
			'FROM '.$this->t_estimate.' AS e '.
				'LEFT JOIN '.$this->t_users.' AS u ON u.id=e.user_id '.
			'WHERE 1 '.
			$reqPlus;


			if (!empty($aParams['order'])) {
				$query .= 'ORDER BY '.$aParams['order'].' ';
			}
			else {
				$query .= 'ORDER BY e.id DESC ';
			}

			if (!empty($aParams['limit'])) {
				$query .= 'LIMIT '.$aParams['limit'].' ';
			}
		}

		if (($rs = $this->db->select($query)) === false)
		{
			if ($bCountOnly) {
				return 0;
			}
			else {
				return new recordset(array());
			}
		}

		if ($bCountOnly) {
			return (integer)$rs->num_estimate;
		}
		else {
			return $rs;
		}
	}

	/**
	 * Retourne une demande de devis donnée sous forme de recordset.
	 *
	 * @param integer $iEstimateId
	 * @return object recordset
	 */
	public function getEstimate($iEstimateId)
	{
		return $this->getEstimates(array(
			'id' => $iEstimateId
		));
	}

	/**
	 * Retourne sous forme de recordset les demandes de devis d'un utilisateur donné.
	 *
	 * @param integer $iUserId
	 * @return object recordset
	 */
	public function getUserEstimates($iUserId)
	{
		return $this->getEstimates(array(
			'user_id' => $iUserId
		));
	}

	/**
	 * Indique si une demande de devis donnée existe.
	 *
	 * @param $iEstimateId
	 * @return boolean
	 */
	public function productExists($iEstimateId)
	{
		if (empty($iEstimateId) || $this->getEstimate($iEstimateId)->isEmpty()) {
			return false;
		}

		return true;
	}

	/**
	 * Ajout d'une demande de devis.
	 *
	 * @param array $aData
	 * @return integer
	 */
	public function addEstimate(array $aData)
	{
		$sDateTime = date('Y-m-d H:i:s');

		if (empty($aData['status'])) {
			$aData['status'] = 1;
		}

		$sQuery =
		'INSERT INTO '.$this->t_estimate.' ( '.
			'status, start_at, end_at, user_id, content, created_at, updated_at '.
		') VALUES ( '.
			(integer)$aData['status'].', '.
			'\''.$this->db->escapeStr(oktMysqli::formatDateTime($aData['start_date'])).'\', '.
			'\''.$this->db->escapeStr(oktMysqli::formatDateTime($aData['end_date'])).'\', '.
			'0, '.
			'\''.$this->db->escapeStr(serialize($aData)).'\', '.
			'\''.$this->db->escapeStr($sDateTime).'\', '.
			'\''.$this->db->escapeStr($sDateTime).'\' '.
		'); ';

		if (!$this->db->execute($sQuery)) {
			return false;
		}

		$iNewId = $this->db->getLastID();

		return $iNewId;
	}


} # class
