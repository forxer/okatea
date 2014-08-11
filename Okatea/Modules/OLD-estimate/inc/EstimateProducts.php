<?php

/**
 * @ingroup okt_module_estimate
 * @brief Classe de manipulation des produits
 *
 */
class EstimateProducts
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

	public function __construct($okt, $t_products, $t_accessories)
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;
		
		$this->t_products = $t_products;
		$this->t_accessories = $t_accessories;
	}

	/**
	 * Retourne une liste de produits selon des paramètres donnés.
	 *
	 * @param array $params        	
	 * @param boolean $count_only        	
	 * @return object recordset/integer
	 */
	public function getProducts($params = [], $count_only = false)
	{
		$reqPlus = '';
		
		if (!empty($params['id']))
		{
			$reqPlus .= ' AND p.id=' . (integer) $params['id'] . ' ';
		}
		
		if (isset($params['active']))
		{
			if ($params['active'] == 0)
			{
				$reqPlus .= 'AND p.active=0 ';
			}
			elseif ($params['active'] == 1)
			{
				$reqPlus .= 'AND p.active=1 ';
			}
			elseif ($params['active'] == 2)
			{
				$reqPlus .= '';
			}
		}
		else
		{
			$reqPlus .= 'AND p.active=1 ';
		}
		
		if ($count_only)
		{
			$query = 'SELECT COUNT(p.id) AS num_products ' . 'FROM ' . $this->t_products . ' AS p ' . 'WHERE 1 ' . $reqPlus;
		}
		else
		{
			$query = 'SELECT p.id, p.active, p.title ' . 'FROM ' . $this->t_products . ' AS p ' . 'WHERE 1 ' . $reqPlus;
			
			if (!empty($params['order']))
			{
				$query .= 'ORDER BY ' . $params['order'] . ' ';
			}
			else
			{
				$query .= 'ORDER BY p.title ASC ';
			}
			
			if (!empty($params['limit']))
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
				return new recordset([]);
			}
		}
		
		if ($count_only)
		{
			return (integer) $rs->num_products;
		}
		else
		{
			return $rs;
		}
	}

	/**
	 * Retourne un produit donné sous forme de recordset.
	 *
	 * @param integer $iProductId        	
	 * @param integer $iActive        	
	 * @return object recordset
	 */
	public function getProduct($iProductId, $iActive = 2)
	{
		return $this->getProducts(array(
			'id' => $iProductId,
			'active' => $iActive
		));
	}

	/**
	 * Indique si un produit donné existe.
	 *
	 * @param
	 *        	$iProductId
	 * @return boolean
	 */
	public function productExists($iProductId)
	{
		if (empty($iProductId) || $this->getProduct($iProductId)->isEmpty())
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Ajout d'un produit.
	 *
	 * @param array $aData        	
	 * @return integer
	 */
	public function addProduct($aData)
	{
		if (!$this->checkPostData($aData))
		{
			return false;
		}
		
		$oCursor = $this->openCursor($aData);
		
		if (!$oCursor->insert())
		{
			return false;
		}
		
		return $this->db->getLastID();
	}

	/**
	 * Modification d'un produit.
	 *
	 * @param array $aData        	
	 * @return boolean
	 */
	public function updProduct($aData)
	{
		if (!$this->productExists($aData['id']))
		{
			$this->error->set(sprintf(__('m_estimate_product_%s_not_exists'), $aData['id']));
			return false;
		}
		
		if (!$this->checkPostData($aData))
		{
			return false;
		}
		
		$oCursor = $this->openCursor($aData);
		
		if (!$oCursor->update('WHERE id=' . (integer) $aData['id']))
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Switch le statut de visibilité d'un produit donné.
	 *
	 * @param integer $id        	
	 * @return boolean
	 */
	public function switchProductStatus($id)
	{
		if (!$this->productExists($id))
		{
			$this->error->set(sprintf(__('m_estimate_product_%s_not_exists'), $id));
			return false;
		}
		
		$query = 'UPDATE ' . $this->t_products . ' SET ' . 'active = 1-active ' . 'WHERE id=' . (integer) $id;
		
		if (!$this->db->execute($query))
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Suppression d'un produit donné.
	 *
	 * @param integer $id        	
	 * @return boolean
	 */
	public function delProduct($id)
	{
		if (!$this->productExists($id))
		{
			$this->error->set(sprintf(__('m_estimate_product_%s_not_exists'), $id));
			return false;
		}
		
		$query = 'DELETE FROM ' . $this->t_products . ' ' . 'WHERE id=' . (integer) $id;
		
		if (!$this->db->execute($query))
		{
			return false;
		}
		
		$this->db->optimize($this->t_products);
		
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
		$oCursor = $this->db->openCursor($this->t_products);
		
		if (!empty($aData) && is_array($aData))
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
			$this->error->set(__('m_estimate_product_must_enter_title'));
		}
		
		return $this->error->isEmpty();
	}
}
