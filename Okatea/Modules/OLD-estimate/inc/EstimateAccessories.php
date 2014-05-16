<?php

/**
 * @ingroup okt_module_estimate
 * @brief Classe de manipulation des accessoires
 *
 */
class EstimateAccessories
{

	/**
	 * Okatea application instance.
	 * 
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * Référence de l'objet de gestion de base de données.
	 * 
	 * @var mysql
	 */
	protected $db;

	/**
	 * Référence de l'objet de gestion des erreurs.
	 * 
	 * @var oktErrors
	 */
	protected $error;

	/**
	 * Le nom de la table des produits.
	 * 
	 * @var string
	 */
	protected $t_products;

	/**
	 * Le nom de la table des accessoires.
	 * 
	 * @var string
	 */
	protected $t_accessories;

	public function __construct($okt, $t_accessories, $t_products)
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;
		
		$this->t_products = $t_products;
		$this->t_accessories = $t_accessories;
	}

	/**
	 * Retourne une liste d'accessoires selon des paramètres donnés.
	 *
	 * @param array $params        	
	 * @param boolean $count_only        	
	 * @return object recordset/integer
	 */
	public function getAccessories($params = array(), $count_only = false)
	{
		$reqPlus = '';
		
		if (! empty($params['id']))
		{
			$reqPlus .= ' AND a.id=' . (integer) $params['id'] . ' ';
		}
		
		if (! empty($params['product_id']))
		{
			$reqPlus .= ' AND a.product_id=' . (integer) $params['product_id'] . ' ';
		}
		
		if (isset($params['active']))
		{
			if ($params['active'] == 0)
			{
				$reqPlus .= 'AND a.active=0 ';
			}
			elseif ($params['active'] == 1)
			{
				$reqPlus .= 'AND a.active=1 ';
			}
			elseif ($params['active'] == 2)
			{
				$reqPlus .= '';
			}
		}
		else
		{
			$reqPlus .= 'AND a.active=1 ';
		}
		
		if ($count_only)
		{
			$query = 'SELECT COUNT(a.id) AS num_accessories ' . 'FROM ' . $this->t_accessories . ' AS a ' . 'LEFT JOIN ' . $this->t_products . ' AS p ON p.id=a.product_id ' . 'WHERE 1 ' . $reqPlus;
		}
		else
		{
			$query = 'SELECT a.id, a.product_id, a.active, a.title, ' . 'p.title AS product_title ' . 'FROM ' . $this->t_accessories . ' AS a ' . 'LEFT JOIN ' . $this->t_products . ' AS p ON p.id=a.product_id ' . 'WHERE 1 ' . $reqPlus;
			
			if (! empty($params['order']))
			{
				$query .= 'ORDER BY ' . $params['order'] . ' ';
			}
			else
			{
				$query .= 'ORDER BY p.title ASC, a.title ASC ';
			}
			
			if (! empty($params['limit']))
			{
				$query .= 'LIMIT ' . $params['limit'] . ' ';
			}
		}
		
		if (($rs = $this->db->select($query)) === false)
		{
			if ($count_only)
			{
				return 0;
			}
			else
			{
				return new recordset(array());
			}
		}
		
		if ($count_only)
		{
			return (integer) $rs->num_accessories;
		}
		else
		{
			return $rs;
		}
	}

	/**
	 * Retourne un accessoire donné sous forme de recordset.
	 *
	 * @param integer $iAccessoryId        	
	 * @param integer $iActive        	
	 * @return object recordset
	 */
	public function getAccessory($iAccessoryId, $iActive = 2)
	{
		return $this->getAccessories(array(
			'id' => $iAccessoryId,
			'active' => $iActive
		));
	}

	/**
	 * Indique si un accessoire donné existe.
	 *
	 * @param
	 *        	$iAccessoryId
	 * @return boolean
	 */
	public function accessoryExists($iAccessoryId)
	{
		if (empty($iAccessoryId) || $this->getAccessory($iAccessoryId)->isEmpty())
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Ajout d'un accessoire.
	 *
	 * @param array $aData        	
	 * @return integer
	 */
	public function addAccessory($aData)
	{
		if (! $this->checkPostData($aData))
		{
			return false;
		}
		
		$oCursor = $this->openCursor($aData);
		
		if (! $oCursor->insert())
		{
			return false;
		}
		
		return $this->db->getLastID();
	}

	/**
	 * Modification d'un accessoire.
	 *
	 * @param array $aData        	
	 * @return boolean
	 */
	public function updAccessory($aData)
	{
		if (! $this->accessoryExists($aData['id']))
		{
			$this->error->set(sprintf(__('m_estimate_accessory_%s_not_exists'), $aData['id']));
			return false;
		}
		
		if (! $this->checkPostData($aData))
		{
			return false;
		}
		
		$oCursor = $this->openCursor($aData);
		
		if (! $oCursor->update('WHERE id=' . (integer) $aData['id']))
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Switch le statut de visibilité d'un accessoire donné.
	 *
	 * @param integer $id        	
	 * @return boolean
	 */
	public function switchAccessoryStatus($id)
	{
		if (! $this->accessoryExists($id))
		{
			$this->error->set(sprintf(__('m_estimate_accessory_%s_not_exists'), $id));
			return false;
		}
		
		$query = 'UPDATE ' . $this->t_accessories . ' SET ' . 'active = 1-active ' . 'WHERE id=' . (integer) $id;
		
		if (! $this->db->execute($query))
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Suppression d'un accessoire donné.
	 *
	 * @param integer $id        	
	 * @return boolean
	 */
	public function delAccessory($id)
	{
		if (! $this->accessoryExists($id))
		{
			$this->error->set(sprintf(__('m_estimate_accessory_%s_not_exists'), $id));
			return false;
		}
		
		$query = 'DELETE FROM ' . $this->t_accessories . ' ' . 'WHERE id=' . (integer) $id;
		
		if (! $this->db->execute($query))
		{
			return false;
		}
		
		$this->db->optimize($this->t_accessories);
		
		return true;
	}

	/**
	 * Créer une instance de cursor et la retourne.
	 *
	 * @param array $data        	
	 * @return object cursor
	 */
	protected function openCursor($aData = null)
	{
		$oCursor = $this->db->openCursor($this->t_accessories);
		
		if (! empty($aData) && is_array($aData))
		{
			foreach ($aData as $k => $v)
			{
				$oCursor->$k = $v;
			}
		}
		
		return $oCursor;
	}

	/**
	 * Vérifie les données envoyées en POST.
	 *
	 * @param array $aData        	
	 * @return boolean
	 */
	protected function checkPostData($aData)
	{
		if (empty($aData['title']))
		{
			$this->error->set(__('m_estimate_accessory_must_enter_title'));
		}
		
		if (empty($aData['product_id']))
		{
			$this->error->set(__('m_estimate_accessory_must_choose_title'));
		}
		
		return $this->error->isEmpty();
	}
}
