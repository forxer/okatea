<?php
/**
 * @ingroup okt_module_estimate
 * @brief La classe principale du module.
 *
 */
use Okatea\Admin\Menu as AdminMenu;
use Okatea\Admin\Page;
use Okatea\Tao\Database\MySqli;
use Okatea\Tao\Database\Recordset;
use Okatea\Tao\Modules\Module;
use Okatea\Tao\Routing\Route;

class module_estimate extends Module
{

	public $config = null;

	public $filters = null;

	protected $t_estimate;

	protected $t_products;

	protected $t_accessories;

	protected $t_users;

	protected function prepend()
	{
		# autoload
		$this->okt->autoloader->addClassMap(array(
			'EstimateAccessories' => __DIR__ . '/inc/EstimateAccessories.php',
			'EstimateController' => __DIR__ . '/inc/EstimateController.php',
			'EstimateFilters' => __DIR__ . '/inc/EstimateFilters.php',
			'EstimateHelpers' => __DIR__ . '/inc/EstimateHelpers.php',
			'EstimateProducts' => __DIR__ . '/inc/EstimateProducts.php'
		));
		
		# permissions
		$this->okt->addPermGroup('estimate', __('m_estimate_perm_group'));
		$this->okt->addPerm('estimate', __('m_estimate_perm_global'), 'estimate');
		$this->okt->addPerm('estimate_products', __('m_estimate_perm_products'), 'estimate');
		$this->okt->addPerm('estimate_accessories', __('m_estimate_perm_accessories'), 'estimate');
		$this->okt->addPerm('estimate_config', 'Configuration', 'estimate');
		
		# les tables
		$this->t_estimate = $this->db->prefix . 'mod_estimate';
		$this->t_products = $this->db->prefix . 'mod_estimate_products';
		$this->t_accessories = $this->db->prefix . 'mod_estimate_accessories';
		$this->t_users = $this->db->prefix . 'core_users';
		
		# config
		$this->config = $this->okt->newConfig('conf_estimate');
		
		$this->products = new EstimateProducts($this->okt, $this->t_products, $this->t_accessories);
		
		if ($this->config->enable_accessories)
		{
			$this->accessories = new EstimateAccessories($this->okt, $this->t_accessories, $this->t_products);
		}
	}

	protected function prepend_admin()
	{
		# on ajoutent un item au menu admin
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->estimateSubMenu = new AdminMenu(null, Page::$formatHtmlSubMenu);
			$this->okt->page->mainMenu->add(__('m_estimate_menu_Estimates'), 'module.php?m=estimate', $this->bCurrentlyInUse, 30, $this->okt->checkPerm('estimate'), null, $this->okt->page->estimateSubMenu, $this->okt->options->public_url . '/modules/' . $this->id() . '/module_icon.png');
			$this->okt->page->estimateSubMenu->add(__('m_estimate_menu_Estimates_list'), 'module.php?m=estimate&amp;action=index', $this->bCurrentlyInUse && (! $this->okt->page->action || $this->okt->page->action === 'index' || $this->okt->page->action === 'estimate'), 1);
			$this->okt->page->estimateSubMenu->add(__('m_estimate_menu_Products'), 'module.php?m=estimate&amp;action=products', $this->bCurrentlyInUse && ($this->okt->page->action === 'products' || $this->okt->page->action === 'product'), 2, $this->okt->checkPerm('estimate_products'));
			$this->okt->page->estimateSubMenu->add(__('m_estimate_menu_Accessories'), 'module.php?m=estimate&amp;action=accessories', $this->bCurrentlyInUse && ($this->okt->page->action === 'accessories' || $this->okt->page->action === 'accessory'), 3, $this->config->enable_accessories && $this->okt->checkPerm('estimate_accessories'));
			$this->okt->page->estimateSubMenu->add(__('c_a_menu_configuration'), 'module.php?m=estimate&amp;action=config', $this->bCurrentlyInUse && ($this->okt->page->action === 'config'), 10, $this->okt->checkPerm('estimate_config'));
		}
	}

	protected function prepend_public()
	{
		$this->okt->page->loadCaptcha($this->config->captcha);
	}

	/**
	 * Initialisation des filtres.
	 *
	 * @param string $sPart
	 *        	'public' ou 'admin'
	 */
	public function filtersStart($sPart = 'public')
	{
		if ($this->filters === null || ! ($this->filters instanceof EstimateFilters))
		{
			$this->filters = new EstimateFilters($this->okt, $sPart);
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
	public function getEstimatesRecordset(array $aParams = array(), $bCountOnly = false)
	{
		$sReqPlus = '';
		
		if (! empty($aParams['id']))
		{
			$sReqPlus .= ' AND e.id=' . (integer) $aParams['id'] . ' ';
		}
		
		if (! empty($aParams['user_id']))
		{
			$sReqPlus .= ' AND e.user_id=' . (integer) $aParams['user_id'] . ' ';
		}
		
		if (isset($aParams['status']))
		{
			if ($aParams['status'] == 0)
			{
				$sReqPlus .= 'AND e.status=0 ';
			}
			elseif ($aParams['status'] == 1)
			{
				$sReqPlus .= 'AND e.status=1 ';
			}
			elseif ($aParams['status'] == 2)
			{
				$sReqPlus .= '';
			}
		}
		
		if ($bCountOnly)
		{
			$query = 'SELECT COUNT(e.id) AS num_estimate ' . 'FROM ' . $this->t_estimate . ' AS e ' . 'LEFT JOIN ' . $this->t_users . ' AS u ON u.id=e.user_id ' . 'WHERE 1 ' . $sReqPlus;
		}
		else
		{
			$query = 'SELECT e.id, e.status, e.start_at, e.end_at, ' . 'e.user_id, e.content, e.created_at, e.updated_at, ' . 'u.username, u.lastname, u.firstname, u.email ' . 'FROM ' . $this->t_estimate . ' AS e ' . 'LEFT JOIN ' . $this->t_users . ' AS u ON u.id=e.user_id ' . 'WHERE 1 ' . $sReqPlus;
			
			if (! empty($aParams['order']))
			{
				$query .= 'ORDER BY ' . $aParams['order'] . ' ';
			}
			else
			{
				$query .= 'ORDER BY e.id DESC ';
			}
			
			if (! empty($aParams['limit']))
			{
				$query .= 'LIMIT ' . $aParams['limit'] . ' ';
			}
		}
		
		if (($rs = $this->db->select($query)) === false)
		{
			if ($bCountOnly)
			{
				return 0;
			}
			else
			{
				return new recordset(array());
			}
		}
		
		if ($bCountOnly)
		{
			return (integer) $rs->num_estimate;
		}
		else
		{
			return $rs;
		}
	}

	/**
	 * Retourne une liste de demande de devis sous forme de recordset selon des paramètres donnés
	 * et les prépares en vue d'un affichage.
	 *
	 * @param array $aParams
	 *        	Paramètres de requete
	 * @return object recordset
	 */
	public function getEstimates($aParams = array())
	{
		$rs = $this->getEstimatesRecordset($aParams);
		
		$this->prepareEstimates($rs);
		
		return $rs;
	}

	/**
	 * Retourne un compte du nombre de demande de devis selon des paramètres donnés.
	 *
	 * @param array $aParams
	 *        	Paramètres de requete
	 * @return integer
	 */
	public function getEstimatesCount($aParams = array())
	{
		return $this->getEstimatesRecordset($aParams, true);
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
	 * @param
	 *        	$iEstimateId
	 * @return boolean
	 */
	public function estimateExists($iEstimateId)
	{
		if (empty($iEstimateId) || $this->getEstimatesRecordset(array(
			'id' => $iEstimateId
		))->isEmpty())
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Formatage des données d'un recordset en vue d'un affichage d'une liste.
	 *
	 * @param recordset $rsEstimates        	
	 * @return void
	 */
	public function prepareEstimates(recordset $rsEstimates)
	{
		$iCountLine = 0;
		while ($rsEstimates->fetch())
		{
			# odd/even
			$rsEstimates->odd_even = ($iCountLine % 2 == 0 ? 'even' : 'odd');
			$iCountLine ++;
			
			# formatages génériques
			$this->commonPreparation($rsEstimates);
		}
	}

	/**
	 * Formatages des données d'un recordset communs aux listes et aux éléments.
	 *
	 * @param recordset $rsEstimates        	
	 * @return void
	 */
	protected function commonPreparation(recordset $rsEstimates)
	{
		$rsEstimates->content = unserialize($rsEstimates->content);
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
		
		if (empty($aData['status']))
		{
			$aData['status'] = 0;
		}
		
		$sQuery = 'INSERT INTO ' . $this->t_estimate . ' ( ' . 'status, start_at, end_at, user_id, content, created_at, updated_at ' . ') VALUES ( ' . (integer) $aData['status'] . ', ' . '\'' . $this->db->escapeStr(MySqli::formatDateTime($aData['start_date'])) . '\', ' . '\'' . $this->db->escapeStr(MySqli::formatDateTime($aData['end_date'])) . '\', ' . '0, ' . '\'' . $this->db->escapeStr(serialize($aData)) . '\', ' . '\'' . $this->db->escapeStr($sDateTime) . '\', ' . '\'' . $this->db->escapeStr($sDateTime) . '\' ' . '); ';
		
		if (! $this->db->execute($sQuery))
		{
			return false;
		}
		
		$iNewId = $this->db->getLastID();
		
		return $iNewId;
	}

	/**
	 * Définit le statut d'une demande de devis donnée.
	 *
	 * @param integer $iPageId        	
	 * @param integer $iStatus        	
	 * @return boolean
	 */
	public function setEstimateStatus($iEstimateId, $iStatus)
	{
		if (! $this->estimateExists($iEstimateId))
		{
			throw new Exception(sprintf(__('m_estimate_estimate_%s_not_exists'), $iEstimateId));
		}
		
		$sQuery = 'UPDATE ' . $this->t_estimate . ' SET ' . 'updated_at=NOW(), ' . 'status = ' . ($iStatus == 1 ? 1 : 0) . ' ' . 'WHERE id=' . (integer) $iEstimateId;
		
		if (! $this->db->execute($sQuery))
		{
			throw new Exception('Unable to update estimate in database.');
		}
		
		return true;
	}

	/**
	 * Marque une demande de devis donnée comme traitée.
	 *
	 * @param integer $iEstimateId        	
	 * @return boolean
	 */
	public function markAsTreated($iEstimateId)
	{
		return $this->setEstimateStatus($iEstimateId, 1);
	}

	/**
	 * Marque une demande de devis donnée comme non traitée.
	 *
	 * @param integer $iEstimateId        	
	 * @return boolean
	 */
	public function markAsUntreated($iEstimateId)
	{
		return $this->setEstimateStatus($iEstimateId, 0);
	}

	/**
	 * Suppression d'une demande de devis donnée.
	 *
	 * @param integer $iEstimateId        	
	 * @return boolean
	 */
	public function deleteEstimate($iEstimateId)
	{
		if (! $this->estimateExists($iEstimateId))
		{
			throw new Exception(sprintf(__('m_estimate_estimate_%s_not_exists'), $iEstimateId));
		}
		
		$sQuery = 'DELETE FROM ' . $this->t_estimate . ' ' . 'WHERE id=' . (integer) $iEstimateId;
		
		if (! $this->db->execute($sQuery))
		{
			throw new Exception('Unable to remove estimate from database.');
		}
		
		$this->db->optimize($this->t_estimate);
		
		return true;
	}
	
	/* Utilitaires
	------------------------------------------------------------*/
	
	/**
	 * Retourne la liste des types de statuts au pluriel
	 *
	 * @param boolean $flip        	
	 * @return array
	 */
	public static function getEstimatesStatuses($flip = false)
	{
		$aStatus = array(
			0 => __('m_estimate_untreateds'),
			1 => __('m_estimate_treateds')
		);
		
		if ($flip)
		{
			$aStatus = array_flip($aStatus);
		}
		
		return $aStatus;
	}

	/**
	 * Retourne la liste des types de statuts au singulier
	 *
	 * @param boolean $flip        	
	 * @return array
	 */
	public static function getEstimatesStatus($flip = false)
	{
		$aStatus = array(
			0 => __('m_estimate_untreated'),
			1 => __('m_estimate_treated')
		);
		
		if ($flip)
		{
			$aStatus = array_flip($aStatus);
		}
		
		return $aStatus;
	}
}
