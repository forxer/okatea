<?php
/**
 * @ingroup okt_module_partners
 * @brief La classe principale du module.
 *
 */
use Okatea\Admin\Menu as AdminMenu;
use Okatea\Admin\Page;
use Okatea\Tao\Database\MySqli;
use Okatea\Tao\Images\ImageUpload;
use Okatea\Tao\Misc\NestedTree;
use Okatea\Tao\Modules\Module;
use Okatea\Tao\Routing\Route;

class module_partners extends Module
{

	protected $t_partners;

	protected $t_partners_locales;

	protected $t_categories;

	protected $t_categories_locales;

	protected $locales = null;

	protected $params = array();

	public $tree;

	protected function prepend()
	{
		# autoload
		$this->okt->autoloader->addClassMap(array(
			'PartnersController' => __DIR__ . '/inc/PartnersController.php',
			'PartnersHelpers' => __DIR__ . '/inc/PartnersHelpers.php',
			'PartnersRecordset' => __DIR__ . '/inc/PartnersRecordset.php'
		));
		
		# permissions
		$this->okt->addPermGroup('partners', __('m_partners_perm_group'));
		$this->okt->addPerm('partners', __('m_partners_perm_global'), 'partners');
		$this->okt->addPerm('partners_add', __('m_partners_perm_add'), 'partners');
		$this->okt->addPerm('partners_remove', __('m_partners_perm_remove'), 'partners');
		$this->okt->addPerm('partners_display', __('m_partners_perm_display'), 'partners');
		$this->okt->addPerm('partners_config', __('m_partners_perm_config'), 'partners');
		
		# tables
		$this->t_partners = $this->db->prefix . 'mod_partners';
		$this->t_partners_locales = $this->db->prefix . 'mod_partners_locales';
		$this->t_categories = $this->db->prefix . 'mod_partners_categories';
		$this->t_categories_locales = $this->db->prefix . 'mod_partners_categories_locales';
		
		# config
		$this->config = $this->okt->newConfig('conf_partners');
		
		# initialisation arbre catégories
		$this->tree = new NestedTree($this->okt, $this->t_categories, 'id', 'parent_id', 'ord', array(
			'active',
			'ord'
		));
	}

	protected function prepend_admin()
	{
		# on ajoute un item au menu admin
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->partnersSubMenu = new AdminMenu(null, Page::$formatHtmlSubMenu);
			$this->okt->page->mainMenu->add($this->getName(), 'module.php?m=partners', $this->bCurrentlyInUse, 10, $this->okt->checkPerm('partners'), null, $this->okt->page->partnersSubMenu, $this->okt->options->public_url . '/modules/' . $this->id() . '/module_icon.png');
			$this->okt->page->partnersSubMenu->add(__('c_a_menu_management'), 'module.php?m=partners&amp;action=index', $this->bCurrentlyInUse && (! $this->okt->page->action || $this->okt->page->action === 'index' || $this->okt->page->action === 'edit'), 1);
			$this->okt->page->partnersSubMenu->add(__('m_partners_add_partner'), 'module.php?m=partners&amp;action=add', $this->bCurrentlyInUse && ($this->okt->page->action === 'add'), 2, $this->okt->checkPerm('partners_add'));
			$this->okt->page->partnersSubMenu->add(__('m_partners_Categories'), 'module.php?m=partners&amp;action=categories', $this->bCurrentlyInUse && ($this->okt->page->action === 'categories'), 3, ($this->config->enable_categories && $this->okt->checkPerm('partners_add')));
			$this->okt->page->partnersSubMenu->add(__('c_a_menu_display'), 'module.php?m=partners&amp;action=display', $this->bCurrentlyInUse && ($this->okt->page->action === 'display'), 10, $this->okt->checkPerm('partners_display'));
			$this->okt->page->partnersSubMenu->add(__('c_a_menu_configuration'), 'module.php?m=partners&amp;action=config', $this->bCurrentlyInUse && ($this->okt->page->action === 'config'), 20, $this->okt->checkPerm('partners_config'));
		}
	}
	
	/* Gestion des partenaires internationalisés
	----------------------------------------------------------*/
	
	/**
	 * Retourne la liste des actualités en fonction d'un tableau de paramètres.
	 *
	 * @param array $params        	
	 * @param boolean $count_only        	
	 * @return recordset
	 */
	public function getPartners($params = array(), $count_only = false)
	{
		$reqPlus = '';
		
		if (! empty($params['id']))
		{
			$reqPlus .= ' AND p.id=' . (integer) $params['id'] . ' ';
		}
		
		if (! empty($params['name']))
		{
			$reqPlus .= ' AND p.name=\'' . $this->db->escapeStr($params['name']) . '\' ';
		}
		
		if (! empty($params['not_null']))
		{
			$reqPlus .= 'AND (pl.description IS NOT NULL OR pl.url IS NOT NULL) ';
		}
		
		if (empty($params['language']))
		{
			$params['language'] = $this->okt->user->language;
		}
		
		$reqPlus .= 'AND pl.language=\'' . $this->db->escapeStr($params['language']) . '\' ';
		if (isset($params['language']))
		{
			$reqPlus .= 'AND (cl.language=\'' . $this->db->escapeStr($params['language']) . '\' OR p.category_id IS NULL) ';
		}
		
		# active ?
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
			$query = 'SELECT COUNT(p.id) AS num_partners ' . 'FROM ' . $this->t_partners . ' AS p ' . 'LEFT JOIN ' . $this->t_partners_locales . ' AS pl ON pl.partner_id=p.id ' . 'LEFT JOIN ' . $this->t_categories . ' AS c ON p.category_id=c.id ' . 'LEFT JOIN ' . $this->t_categories_locales . ' AS cl ON cl.category_id=c.id ' . 'WHERE 1 ' . $reqPlus;
		}
		else
		{
			$query = 'SELECT p.id, p.active, p.category_id, p.name, p.logo, p.created_at, p.updated_at, ' . 'pl.description, pl.url, pl.url_title, ' . 'cl.name AS category_name ' . 'FROM ' . $this->t_partners . ' AS p ' . 'LEFT JOIN ' . $this->t_categories . ' AS c ON p.category_id=c.id ' . 'LEFT JOIN ' . $this->t_categories_locales . ' AS cl ON cl.category_id=c.id ' . 'LEFT JOIN ' . $this->t_partners_locales . ' AS pl ON pl.partner_id=p.id ' . 'WHERE 1 ' . $reqPlus;
			
			$order_direction = 'ASC';
			if (isset($params['order_direction']) && (strtoupper($params['order_direction']) == 'DESC' || strtoupper($params['order_direction']) == 'ASC'))
			{
				$order_direction = $params['order_direction'];
			}
			
			if (! empty($params['order']))
			{
				$query .= 'ORDER BY ' . $params['order'] . ' ' . $order_direction . ' ';
			}
			elseif ($this->config->enable_categories)
			{
				$query .= 'ORDER BY c.ord ' . $order_direction . ', p.ord ' . $order_direction . ' ';
			}
			else
			{
				$query .= 'ORDER BY p.ord ' . $order_direction . ' ';
			}
			
			if (! empty($params['limit']))
			{
				$query .= 'LIMIT ' . $params['limit'] . ' ';
			}
		}
		
		if (($rs = $this->db->select($query, 'PartnersRecordset')) === false)
		{
			
			if ($count_only)
			{
				return 0;
			}
			else
			{
				$rs = new PartnersRecordset(array());
				$rs->setCore($this->okt);
				return $rs;
			}
		}
		
		if ($count_only)
		{
			return (integer) $rs->num_partners;
		}
		else
		{
			$rs->setCore($this->okt);
			return $rs;
		}
	}

	/**
	 * Retourne les infos d'un partenaire donné
	 *
	 * @param integer $id        	
	 * @return recordset
	 */
	public function getPartner($id)
	{
		return $this->getPartners(array(
			'id' => $id,
			'active' => 2
		));
	}

	/**
	 * Indique si un partenaire existe
	 *
	 * @param
	 *        	$id
	 * @return boolean
	 */
	public function partnerExists($id)
	{
		if ($this->getPartner($id)->isEmpty())
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Retourne les localisations d'un partenaire donné
	 *
	 * @param integer $partner_id        	
	 * @return recordset
	 */
	public function getPartnerInter($partner_id)
	{
		$query = 'SELECT language, description, url, url_title ' . 'FROM ' . $this->t_partners_locales . ' ' . 'WHERE partner_id=' . (integer) $partner_id;
		
		if (($rs = $this->db->select($query)) === false)
		{
			$rs = new recordset(array());
			return $rs;
		}
		
		return $rs;
	}

	/**
	 * Vérifie les paramètres requis pour l'ajout et la modification d'un partenaire.
	 *
	 * @param array $params        	
	 * @return void
	 */
	protected function checkParams()
	{
		if (! empty($this->params))
		{
			# champ name
			if ($this->config->chp_name > 0)
			{
				if ($this->config->chp_name == 2 && empty($this->params['name']))
				{
					$this->error->set(__('m_partners_must_name'));
				}
			}
			else
			{
				$this->params['name'] = null;
			}
			
			# description
			if ($this->config->chp_description > 0)
			{
				if ($this->config->chp_description == 2 && (empty($this->params['descriptions'][$this->okt['config']->language])))
				{
					$this->error->set(sprintf(__('m_partners_error_missing_default_language_description_%s'), $this->okt->languages->list[$this->okt['config']->language]['title']));
				}
			}
			else
			{
				$this->params['descriptions'][$this->okt['config']->language] = null;
			}
			
			# champ URL
			if ($this->config->chp_url > 0)
			{
				foreach ($this->params['urls'] as $k => $v)
				{
					if (! empty($v) && ($this->params['urls'][$k] = filter_var($v, FILTER_VALIDATE_URL)) === false)
					{
						$this->error->set(sprintf(__('m_partners_error_invalid_url_%s'), $this->okt->languages->list[$k]['title']));
					}
				}
				
				if ($this->config->chp_url == 2 && empty($this->params['urls'][$this->okt['config']->language]))
				{
					$this->error->set(sprintf(__('m_partners_error_missing_default_language_url_%s'), $this->okt->languages->list[$this->okt['config']->language]['title']));
				}
			}
			else
			{
				$this->params['urls'][$this->okt['config']->language] = null;
			}
			
			# champ URL title
			if ($this->config->chp_url_title > 0)
			{
				if ($this->config->chp_url_title == 2 && empty($this->params['urls_titles'][$this->okt['config']->language]))
				{
					$this->error->set(sprintf(__('m_partners_error_missing_default_language_url_title_%s'), $this->okt->languages->list[$this->okt['config']->language]['title']));
				}
			}
			else
			{
				$this->params['urls_titles'][$this->okt['config']->language] = null;
			}
		}
	}

	/**
	 * Ajout d'une actualité
	 *
	 * @param array $params
	 *        	'name' => string
	 *        	'logo' => string
	 *        	'descriptions' => array
	 *        	'active' => boolean
	 *        	'date' => string
	 *        	'url' => array
	 *        	'url_title' => array
	 *        	
	 * @return integer
	 */
	public function addPartner($params = array())
	{
		$query = 'SELECT MAX(ord) FROM ' . $this->t_partners;
		$rs = $this->db->select($query);
		if ($rs->isEmpty())
		{
			return false;
		}
		$max_ord = $rs->f(0);
		
		$this->params = $params;
		$this->checkParams();
		
		if (! $this->error->isEmpty())
		{
			return false;
		}
		
		# ajout du partenaire
		$this->params['date'] = ! empty($this->params['date']) ? '\'' . MySqli::formatDateTime($this->params['date']) . '\'' : 'NOW()';
		$query = 'INSERT INTO ' . $this->t_partners . ' ( ' . 'active, category_id, name, created_at, updated_at, ord ' . ') VALUES ( ' . (integer) $this->params['active'] . ', ' . (is_null($this->params['category_id']) ? 'NULL, ' : (integer) $this->params['category_id'] . ', ') . (empty($this->params['name']) ? 'NULL,' : '\'' . $this->db->escapeStr($this->params['name']) . '\', ') . $this->params['date'] . ', ' . 'NOW() ' . ',' . (integer) ($max_ord + 1) . '); ';
		
		if (! $this->db->execute($query))
		{
			return false;
		}
		
		$iNewId = $this->db->getLastID();
		
		$this->params['id'] = $iNewId;
		
		# ajout des textes internationalisés
		if (! $this->setPartnerInter())
		{
			return false;
		}
		# ajout du logo
		if (! $this->addLogo($iNewId))
		{
			return false;
		}
		
		return $iNewId;
	}

	/**
	 * Mise à jour d'un partenaire
	 *
	 * @param array $params
	 *        	'id' => integer
	 *        	'name' => string
	 *        	'logo' => string
	 *        	'descriptions' => array
	 *        	'active' => boolean
	 *        	'date' => string
	 *        	'url' => array
	 *        	'url_title' => array
	 * @return boolean
	 */
	public function updPartner($params = array())
	{
		$this->params = $params;
		if (! $this->partnerExists($this->params['id']))
		{
			$this->error->set('Le partenaire #' . $this->params['id'] . ' n’existe pas.');
			return false;
		}
		
		# vérification des paramètres
		$this->checkParams();
		
		if (! $this->error->isEmpty())
		{
			return false;
		}
		
		$query = 'UPDATE ' . $this->t_partners . ' SET ' . 'active=' . (integer) $this->params['active'] . ', ' . 'category_id=' . (is_null($this->params['category_id']) ? 'NULL,' : (integer) $this->params['category_id'] . ', ') . 'name=' . '\'' . $this->db->escapeStr($this->params['name']) . '\', ' . (! empty($this->params['date']) ? 'created_at=\'' . MySqli::formatDateTime($this->params['date']) . '\', ' : '') . 'updated_at=NOW() ' . 'WHERE id=' . (integer) $this->params['id'];
		
		if (! $this->db->execute($query))
		{
			return false;
		}
		
		# modification des textes internationalisés
		if (! $this->setPartnerInter())
		{
			return false;
		}
		
		# modification du logo
		if ($this->updLogo($this->params['id']) === false)
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Suppression d'une actualité
	 *
	 * @param integer $id        	
	 * @return boolean
	 */
	public function deletePartner($id)
	{
		if (! $this->partnerExists($id))
		{
			return false;
		}
		
		# delete images
		$this->deleteLogo($id, 1);
		
		# delete partner
		$query = 'DELETE FROM ' . $this->t_partners . ' ' . 'WHERE id=' . (integer) $id;
		
		if (! $this->db->execute($query))
		{
			return false;
		}
		
		$this->db->optimize($this->t_partners);
		
		# delete partenaire inter
		$query = 'DELETE FROM ' . $this->t_partners_locales . ' ' . 'WHERE partner_id=' . (integer) $id;
		
		if (! $this->db->execute($query))
		{
			return false;
		}
		
		$this->db->optimize($this->t_partners_locales);
		
		return true;
	}

	/**
	 * Switch le statut d'un partenaire donné
	 *
	 * @param integer $partner_id        	
	 * @return boolean
	 */
	public function setPartnerStatus($partner_id)
	{
		$query = 'UPDATE ' . $this->t_partners . ' SET ' . 'active = 1-active ' . 'WHERE id=' . (integer) $partner_id;
		
		if (! $this->db->execute($query))
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Change l'ordre d'un partenaire donné
	 *
	 * @param integer $partner_id        	
	 * @param integer $ord        	
	 * @return boolean
	 */
	public function updPartnersOrder($partner_id, $ord)
	{
		$query = 'UPDATE ' . $this->t_partners . ' SET ' . 'ord=' . (integer) $ord . ' ' . 'WHERE id=' . (integer) $partner_id;
		
		if (! $this->db->execute($query))
		{
			return false;
		}
		
		return true;
	}
	
	/* CATEGORIES */
	
	/**
	 * Récupération de catégories.
	 *
	 * @param
	 *        	$params
	 * @param
	 *        	$count_only
	 * @return recordset
	 */
	public function getCategories($params = array(), $count_only = false)
	{
		$reqPlus = '';
		
		$with_count = isset($params['with_count']) ? (boolean) $params['with_count'] : false;
		
		if (! empty($params['id']))
		{
			$reqPlus .= 'AND c.id=' . (integer) $params['id'] . ' ';
			$with_count = false;
		}
		
		if (! empty($params['language']))
		{
			$reqPlus .= 'AND cl.language=\'' . $this->db->escapeStr($params['language']) . '\' ';
		}
		
		if (isset($params['active']))
		{
			if ($params['active'] == 0)
			{
				$reqPlus .= 'AND c.active=0 ';
				$with_count = false;
			}
			elseif ($params['active'] == 1)
			{
				$reqPlus .= 'AND c.active=1 ';
				$with_count = false;
			}
			elseif ($params['active'] == 2)
			{
				$reqPlus .= '';
			}
		}
		else
		{
			$reqPlus .= 'AND c.active=1 ';
			$with_count = false;
		}
		
		if ($count_only)
		{
			$query = 'SELECT COUNT(id) AS num_categories ' . 'FROM ' . $this->t_categories . ' AS r ' . 'WHERE 1 ';
		}
		else
		{
			$query = 'SELECT c.id, c.active, c.ord, c.parent_id, c.level, ' . 'cl.language, cl.name, ' . 'COUNT(p.id) AS num_items ' . 'FROM ' . $this->t_categories . ' AS c ' . 'LEFT JOIN ' . $this->t_categories_locales . ' AS cl ON cl.category_id=c.id ' . 'LEFT JOIN ' . $this->t_partners . ' AS p ON c.id=p.category_id ' . 'WHERE 1 ' . $reqPlus . ' ' . 'GROUP BY c.id ' . 'ORDER BY nleft asc ';
			
			if (! empty($params['limit']))
			{
				$query .= 'LIMIT ' . $params['limit'] . ' ';
			}
		}
		if (($rs = $this->db->select($query)) === false)
		{
			return new recordset(array());
		}
		
		if ($count_only)
		{
			return (integer) $rs->num_categories;
		}
		else
		{
			if ($with_count)
			{
				$data = array();
				$stack = array();
				$level = 0;
				foreach (array_reverse($rs->getData()) as $category)
				{
					$num_items = (integer) $category['num_items'];
					
					if ($category['level'] > $level)
					{
						$nb_total = $num_items;
						$stack[$category['level']] = $num_items;
					}
					elseif ($category['level'] == $level)
					{
						$nb_total = $num_items;
						$stack[$category['level']] += $num_items;
					}
					else
					{
						$nb_total = $stack[$category['level'] + 1] + $num_items;
						if (isset($stack[$category['level']]))
						{
							$stack[$category['level']] += $nb_total;
						}
						else
						{
							$stack[$category['level']] = $nb_total;
						}
						unset($stack[$category['level'] + 1]);
					}
					
					$level = $category['level'];
					
					$category['num_items'] = $num_items;
					$category['num_total'] = $nb_total;
					
					array_unshift($data, $category);
				}
				
				return new recordset($data);
			}
			else
			{
				return $rs;
			}
		}
	}

	/**
	 * Retourne une catégorie
	 * 
	 * @param integer $id        	
	 */
	public function getCategory($id)
	{
		return $this->getCategories(array(
			'id' => $id,
			'active' => 2
		));
	}

	/**
	 * Retourne les données internationalisées sur une categorie
	 * 
	 * @param integer $category_id        	
	 */
	public function getCategoryLocales($category_id)
	{
		$query = 'SELECT category_id, language, name ' . 'FROM ' . $this->t_categories_locales . ' ' . 'WHERE category_id=' . (integer) $category_id;
		
		if (($rs = $this->db->select($query)) === false)
		{
			return new recordset(array());
		}
		
		return $rs;
	}

	public function categoryExists($id)
	{
		if ($this->getCategory($id)->isEmpty())
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Ajout d'une catégorie
	 *
	 * @param integer $active        	
	 * @param string $name        	
	 * @return integer
	 */
	public function addCategory($active, $names, $parent_id = 0)
	{
		$max_ord = $this->numChildren($parent_id);
		
		# infos parents
		if ($parent_id > 0)
		{
			$parent = $this->getCategory($parent_id);
			
			if ($parent->active == 0)
			{
				$active = 0;
			}
		}
		
		$query = 'INSERT INTO ' . $this->t_categories . ' ( ' . 'active, parent_id, ord ' . ') VALUES ( ' . (integer) $active . ', ' . (integer) $parent_id . ', ' . ($max_ord + 1) . '); ';
		
		if (! $this->db->execute($query))
		{
			return false;
		}
		
		$new_id = $this->db->getLastID();
		
		$this->rebuildTree();
		
		foreach ($names as $lang => $name)
		{
			if (! empty($name))
			{
				$query = 'INSERT INTO ' . $this->t_categories_locales . ' ( ' . 'category_id, language, name ' . ') VALUES ( ' . (integer) $new_id . ', ' . '\'' . $this->db->escapeStr($lang) . '\', ' . '\'' . $this->db->escapeStr($name) . '\'' . '); ';
				
				if (! $this->db->execute($query))
				{
					return false;
				}
			}
		}
		
		return $new_id;
	}

	/**
	 * Modification d'une catégorie
	 *
	 * @param integer $id        	
	 * @param integer $active        	
	 * @param string $name        	
	 * @return boolean
	 */
	public function updCategory($id, $active, $names, $parent_id)
	{
		if (! $this->categoryExists($id))
		{
			$this->error->set('La catégorie #' . $id . ' n’existe pas.');
			return false;
		}
		
		# infos parent
		if ($parent_id > 0)
		{
			if ($this->isDescendantOf($parent_id, $id))
			{
				$this->error->set('Vous ne pouvez pas mettre une catégorie dans ses enfants.');
				return false;
			}
			
			$parent = $this->getCategory($parent_id);
			
			if ($parent->active == 0)
			{
				$active = 0;
			}
		}
		
		$query = 'UPDATE ' . $this->t_categories . ' SET ' . 'active=' . (integer) $active . ', ' . 'parent_id=' . (integer) $parent_id . ' ' . 'WHERE id=' . (integer) $id;
		
		if (! $this->db->execute($query))
		{
			return false;
		}
		
		if ($active == 0)
		{
			$childrens = $this->getDescendants($id);
			while ($childrens->fetch())
			{
				$this->setCategoryStatus($childrens->id, $active);
			}
		}
		
		foreach ($names as $lang => $name)
		{
			if (! empty($name))
			{
				$query = 'INSERT INTO ' . $this->t_categories_locales . ' ( ' . 'category_id, language, name ' . ') VALUES ( ' . (integer) $id . ', ' . '\'' . $this->db->escapeStr($lang) . '\', ' . '\'' . $this->db->escapeStr($name) . '\'' . ') ON DUPLICATE KEY UPDATE ' . 'name=\'' . $this->db->escapeStr($name) . '\'';
				
				if (! $this->db->execute($query))
				{
					return false;
				}
			}
		}
		$this->rebuildTree();
		
		return true;
	}

	/**
	 * Mise à jour de l'ordre d'une catégorie
	 * 
	 * @param integer $id        	
	 * @param integer $ord        	
	 */
	public function updCategoryOrder($id, $ord)
	{
		$query = 'UPDATE ' . $this->t_categories . ' SET ' . 'ord=' . (integer) $ord . ' ' . 'WHERE id=' . (integer) $id;
		
		if (! $this->db->execute($query))
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Switch le statut de visibilité d'une catégorie donnée
	 *
	 * @param integer $id        	
	 * @return boolean
	 */
	public function switchCategoryStatus($id)
	{
		$rsCategory = $this->getCategory($id);
		
		if ($rsCategory->isEmpty())
		{
			$this->error->set('La catégorie #' . $id . ' n’existe pas.');
			return false;
		}
		
		$status = $rsCategory->active ? 0 : 1;
		
		if ($status == 0)
		{
			$childrens = $this->getDescendants($id);
			while ($childrens->fetch())
			{
				$this->setCategoryStatus($childrens->id, 0);
			}
		}
		elseif ($rsCategory->parent_id != 0)
		{
			$rsParent = $this->getCategory($rsCategory->parent_id);
			
			if ($rsParent->active == 0)
			{
				$this->error->set('La catégorie parent est masquée, vous devez la rendre visible avant de le faire pour celle-ci.');
				return false;
			}
		}
		
		return $this->setCategoryStatus($id, $status);
	}

	/**
	 * Défini le statut d'une catégorie
	 * 
	 * @param integer $id        	
	 * @param integer $status        	
	 */
	private function setCategoryStatus($id, $status)
	{
		$query = 'UPDATE ' . $this->t_categories . ' SET ' . 'active=' . (integer) $status . ' ' . 'WHERE id=' . (integer) $id;
		
		if (! $this->db->execute($query))
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Suppression d'une catégorie
	 *
	 * @param integer $id        	
	 * @return boolean
	 */
	public function delCategory($id)
	{
		$rsCategory = $this->getCategory($id);
		
		if ($rsCategory->isEmpty())
		{
			$this->error->set('La catégorie #' . $id . ' n’existe pas.');
			return false;
		}
		
		$childrens = $this->getChildren($id);
		while ($childrens->fetch())
		{
			$this->setParentId($childrens->id, $rsCategory->parent_id);
		}
		
		$query = 'DELETE FROM ' . $this->t_categories . ' ' . 'WHERE id=' . (integer) $id;
		
		if (! $this->db->execute($query))
		{
			return false;
		}
		
		$this->db->optimize($this->t_categories);
		$this->db->optimize($this->t_categories_locales);
		
		$this->rebuildTree();
		
		return true;
	}

	private function setParentId($id, $parent_id)
	{
		$query = 'UPDATE ' . $this->t_categories . ' SET ' . 'parent_id=' . (integer) $parent_id . ' ' . 'WHERE id=' . (integer) $id;
		
		if (! $this->db->execute($query))
		{
			return false;
		}
		
		return true;
	}

	public function getDescendants($id = 0, $includeSelf = false)
	{
		return $this->tree->getDescendants($id, $includeSelf, false);
	}

	public function getChildren($id = 0, $includeSelf = false)
	{
		return $this->tree->getChildren($id, $includeSelf);
	}

	public function getPath($id = 0, $includeSelf = false)
	{
		return $this->tree->getPath($id, $includeSelf);
	}

	public function isDescendantOf($descendant_id, $ancestor_id)
	{
		return $this->tree->isDescendantOf($descendant_id, $ancestor_id);
	}

	public function isChildOf($child_id, $parent_id)
	{
		return $this->tree->isChildOf($child_id, $parent_id);
	}

	public function numDescendants($id)
	{
		return $this->tree->numDescendants($id);
	}

	public function numChildren($id)
	{
		return $this->tree->numChildren($id);
	}

	public function rebuildTree()
	{
		return $this->tree->rebuild();
	}
	
	/* Gestion des logos
	----------------------------------------------------------*/
	
	/**
	 * Retourne une instance de la classe oktImageUpload
	 *
	 * @return object oktImageUpload
	 */
	public function getLogoUpload()
	{
		$o = new ImageUpload($this->okt, $this->config->images);
		$o->setConfig(array(
			'upload_dir' => $this->upload_dir . '/img',
			'upload_url' => $this->upload_url . '/img'
		));
		
		return $o;
	}

	/**
	 * Ajout du logo d'une partenaire donnée
	 *
	 * @param
	 *        	$partner_id
	 * @return boolean
	 */
	public function addLogo($partner_id)
	{
		$aImages = $this->getLogoUpload()->addImages($partner_id);
		
		if (! $this->error->isEmpty())
		{
			return false;
		}
		
		$image = ! empty($aImages[1]) ? $aImages[1] : null;
		
		return $this->updLogos($partner_id, $image);
	}

	/**
	 * Modification du logo d'un partenaire donné
	 *
	 * @param
	 *        	$partner_id
	 * @return boolean
	 */
	public function updLogo($partner_id)
	{
		$aCurrentImages = $this->getLogos($partner_id);
		
		if (! $this->error->isEmpty())
		{
			return false;
		}
		
		$aImages = $this->getLogoUpload()->updImages($partner_id, $aCurrentImages);
		
		if (! $this->error->isEmpty())
		{
			return false;
		}
		
		$image = ! empty($aImages[1]) ? $aImages[1] : null;
		
		return $this->updLogos($partner_id, $image);
	}

	/**
	 * Suppression du logo d'un partenaire donné
	 *
	 * @param
	 *        	$partner_id
	 * @param
	 *        	$img_id
	 * @return boolean
	 */
	public function deleteLogo($partner_id, $img_id)
	{
		$aCurrentImages = $this->getLogos($partner_id);
		
		if (! $this->error->isEmpty())
		{
			return false;
		}
		
		$aNewImages = $this->getLogoUpload()->deleteImage($partner_id, $aCurrentImages, $img_id);
		
		if (! $this->error->isEmpty())
		{
			return false;
		}
		
		$image = ! empty($aNewImages[1]) ? $aNewImages[1] : null;
		
		return $this->updLogos($partner_id, $image);
	}

	/**
	 * Suppression des logo d'un partenaire
	 *
	 * @param
	 *        	$partner_id
	 * @return boolean
	 */
	public function deleteLogos($partner_id)
	{
		$aCurrentImages = $this->getLogos($partner_id);
		
		if (! $this->error->isEmpty())
		{
			return false;
		}
		
		$this->getLogoUpload()->deleteAllImages($partner_id, $aCurrentImages);
		
		return $this->updLogos($partner_id);
	}

	/**
	 * Régénération de toutes les miniatures des logo
	 *
	 * @return void
	 */
	public function regenMinLogos()
	{
		@ini_set('memory_limit', - 1);
		set_time_limit(0);
		
		$rsPartners = $this->getPartners(array(
			'active' => 2
		));
		
		while ($rsPartners->fetch())
		{
			$aImages = $rsPartners->getImagesArray();
			$aImagesList = array();
			
			if (! empty($aImages['img_name']))
			{
				$this->getLogoUpload()->buildThumbnails($rsPartners->id, $aImages['img_name']);
				
				$aImagesList = array_merge($aImages, $this->getLogoUpload()->buildImageInfos($rsPartners->id, $aImages['img_name']));
			}
			
			$this->updLogos($rsPartners->id, $aImagesList);
		}
		
		return true;
	}

	/**
	 * Récupère le logo d'un partenaire donné
	 *
	 * @param
	 *        	$partner_id
	 * @return array
	 */
	public function getLogos($partner_id)
	{
		if (! $this->partnerExists($partner_id))
		{
			$this->error->set('Le partenaire #' . $partner_id . ' n’existe pas.');
			return false;
		}
		
		$rsPartners = $this->getPartner($partner_id);
		
		if ($rsPartners->logo)
		{
			$aItemImages = unserialize($rsPartners->logo);
			return array(
				1 => $aItemImages
			);
		}
		else
		{
			return array();
		}
	}

	/**
	 * Met à jours le logo d'un partenaire donné
	 *
	 * @param integer $partner_id        	
	 * @param array $aImage        	
	 * @return boolean
	 */
	public function updLogos($partner_id, $aImage = null)
	{
		if (! $this->partnerExists($partner_id))
		{
			$this->error->set('Le Partenaire #' . $partner_id . ' n’existe pas.');
			return false;
		}
		
		$aImage = ! empty($aImage) ? serialize($aImage) : NULL;
		
		$query = 'UPDATE ' . $this->t_partners . ' SET ' . 'logo=' . (! is_null($aImage) ? '\'' . $this->db->escapeStr($aImage) . '\'' : 'NULL') . ' ' . 'WHERE id=' . (integer) $partner_id;
		
		if (! $this->db->execute($query))
		{
			return false;
		}
		
		return true;
	}
	
	/* Utilitaires
	----------------------------------------------------------*/
	
	/**
	 * Enregistrement des textes internationalisés
	 *
	 * @return boolean
	 */
	protected function setPartnerInter()
	{
		foreach ($this->okt->languages->list as $aLanguage)
		{
			$this->params['descriptions'][$aLanguage['code']] = $this->okt->HTMLfilter($this->params['descriptions'][$aLanguage['code']]);
			$query = 'INSERT INTO ' . $this->t_partners_locales . ' ' . '(partner_id,language,description,url,url_title) ' . 'VALUES (' . (integer) $this->params['id'] . ', ' . '\'' . $this->db->escapeStr($aLanguage['code']) . '\', ' . (empty($this->params['descriptions'][$aLanguage['code']]) ? 'NULL' : '\'' . $this->db->escapeStr($this->params['descriptions'][$aLanguage['code']]) . '\'') . ', ' . (empty($this->params['urls'][$aLanguage['code']]) ? 'NULL' : '\'' . $this->db->escapeStr($this->params['urls'][$aLanguage['code']]) . '\'') . ', ' . (empty($this->params['urls_titles'][$aLanguage['code']]) ? 'NULL' : '\'' . $this->db->escapeStr($this->params['urls_titles'][$aLanguage['code']]) . '\'') . ' ' . ') ON DUPLICATE KEY UPDATE ' . 'description=' . (empty($this->params['descriptions'][$aLanguage['code']]) ? 'NULL' : '\'' . $this->db->escapeStr($this->params['descriptions'][$aLanguage['code']]) . '\'') . ', ' . 'url=' . (empty($this->params['urls'][$aLanguage['code']]) ? 'NULL' : '\'' . $this->db->escapeStr($this->params['urls'][$aLanguage['code']]) . '\'') . ', ' . 'url_title=' . (empty($this->params['urls_titles'][$aLanguage['code']]) ? 'NULL' : '\'' . $this->db->escapeStr($this->params['urls_titles'][$aLanguage['code']]) . '\'') . ' ';
			
			if (! $this->db->execute($query))
			{
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Retourne la liste des types de statuts au pluriel
	 *
	 * @param boolean $flip        	
	 * @return array
	 */
	public static function getPartnersStatuses($flip = false)
	{
		$aStatus = array(
			0 => __('c_c_status_offline'),
			1 => __('c_c_status_online')
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
	public static function getPartnersStatus($flip = false)
	{
		$aStatus = array(
			0 => __('c_c_status_offline'),
			1 => __('c_c_status_online')
		);
		
		if ($flip)
		{
			$aStatus = array_flip($aStatus);
		}
		
		return $aStatus;
	}
}
