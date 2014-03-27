<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Navigation\Menus;

use Okatea\Tao\Database\Recordset;
use Okatea\Tao\Misc\Utilities;

/**
 * Le gestionnnaire de menus de navigation.
 *
 */
class Menus
{
	/**
	 * Okatea application instance.
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * L'objet gestionnaire de base de données.
	 * @var object
	 */
	protected $db;

	/**
	 * L'objet gestionnaire d'erreurs
	 * @var object
	 */
	protected $error;

	/**
	 * Le nom de la table menus
	 * @var string
	 */
	protected $t_menus;

	/**
	 * Le nom de la table des éléments des menus
	 * @var string
	 */
	protected $t_items;

	/**
	 * Le nom de la table des locales des éléments des menus
	 * @var string
	 */
	protected $t_items_locales;


	/**
	 * Constructor.
	 *
	 * @param Okatea\Tao\Application $okt 	Okatea application instance.
	 * @return void
	 */
	public function __construct($okt)
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;

		# tables
		$this->t_menus = $this->db->prefix.'core_nav_menus';
		$this->t_items = $this->db->prefix.'core_nav_items';
		$this->t_items_locales = $this->db->prefix.'core_nav_items_locales';
	}

	/**
	 * Rendu d'un menu donné.
	 *
	 * @param mixed $mMenu
	 * @param string $sUserTpl
	 * @return string
	 */
	public function render($mMenu, $sUserTpl=null)
	{
		# récupération du menu
		$aMenuParams = array(
			'language' => $this->okt->user->language,
			'active' => 1
		);

		if (Utilities::isInt($mMenu)) {
			$aMenuParams['id'] = $mMenu;
		}
		else {
			$aMenuParams['title'] = $mMenu;
		}

		$rsMenu = $this->getMenus($aMenuParams);

		if ($rsMenu->isEmpty()) {
			return null;
		}

		# récupération des éléments
		$rsItems = $this->getItems(array(
			'menu_id' => $rsMenu->id,
			'language' => $this->okt->user->language,
			'active' => 1
		));

		# affichage du template
		$sTemplate = $this->okt->config->navigation_tpl['default'];

		if (!empty($sUserTpl) && in_array($sUserTpl, $this->okt->config->navigation_tpl['usables'])) {
			$sTemplate = $sUserTpl;
		}
		elseif (!empty($rsMenu->tpl) && in_array($rsMenu->tpl, $this->okt->config->navigation_tpl['usables'])) {
			$sTemplate = $rsMenu->tpl;
		}

		return $this->okt->tpl->render('navigation/'.$sTemplate.'/template', array(
			'rsMenu' => $rsMenu,
			'rsItems' => $rsItems
		));
	}

	/**
	 * Retourne une liste de menus sous forme de recordset.
	 *
	 * @param array $aParams
	 * @return Recordset
	 */
	public function getMenus(array $aParams=array())
	{
		$sReqPlus = '';

		if (!empty($aParams['id'])) {
			$sReqPlus .= ' AND m.id='.(integer)$aParams['id'].' ';
		}

		if (!empty($aParams['title'])) {
			$sReqPlus .= ' AND m.title=\''.$this->db->escapeStr($aParams['title']).'\' ';
		}

		if (isset($aParams['active']))
		{
			if ($aParams['active'] == 0) {
				$sReqPlus .= 'AND m.active=0 ';
			}
			elseif ($aParams['active'] == 1) {
				$sReqPlus .= 'AND m.active=1 ';
			}
			elseif ($aParams['active'] == 2) {
				$sReqPlus .= '';
			}
		}
		else {
			$sReqPlus .= 'AND m.active=1 ';
		}

		$sQuery =
		'SELECT m.id, m.title, m.active, m.tpl, COUNT(i.id) AS num_items '.
		'FROM '.$this->t_menus.' AS m '.
			'LEFT JOIN '.$this->t_items.' AS i ON m.id=i.menu_id '.
		'WHERE 1 '.
		$sReqPlus.' '.
		'GROUP BY m.id ';

		if (($rs = $this->db->select($sQuery)) === false) {
			return new Recordset(array());
		}

		return $rs;
	}

	/**
	 * Retourne un menu donné sous forme de recordset.
	 *
	 * @param integer $iMenuId
	 * @param integer $iActive
	 * @return Recordset
	 */
	public function getMenu($iMenuId, $iActive=2)
	{
		return $this->getMenus(array(
			'id' => $iMenuId,
			'active' => $iActive
		));
	}

	/**
	 * Indique si un menu donné existe.
	 *
	 * @param $iMenuId
	 * @return boolean
	 */
	public function menuExists($iMenuId)
	{
		if (empty($iMenuId) || $this->getMenu($iMenuId)->isEmpty()) {
			return false;
		}

		return true;
	}

	/**
	 * Ajout d'un menu.
	 *
	 * @param array $aData
	 * @return integer
	 */
	public function addMenu($aData)
	{
		$oCursor = $this->openMenuCursor($aData);

		if (!$oCursor->insert()) {
			throw new \Exception('Unable to insert menu into database.');
		}

		return $this->db->getLastID();
	}

	/**
	 * Modification d'un menu.
	 *
	 * @param array $aData
	 * @return boolean
	 */
	public function updMenu($aData)
	{
		if (!$this->menuExists($aData['id'])) {
			throw new \Exception(sprintf(__('c_a_config_navigation_menu_%s_not_exists'), $aData['id']));
		}

		$oCursor = $this->openMenuCursor($aData);

		if (!$oCursor->update('WHERE id='.(integer)$aData['id'])) {
			throw new \Exception('Unable to update menu into database.');
		}

		return true;
	}

	/**
	 * Vérifie les données envoyées en POST pour l'ajout ou la modification d'un menu.
	 *
	 * @param array $aData
	 * @return boolean
	 */
	public function checkPostMenuData($aData)
	{
		if (empty($aData['title'])) {
			$this->error->set(__('c_a_config_navigation_must_enter_title'));
		}

		return $this->error->isEmpty();
	}

	/**
	 * Switch le statut de visibilité d'un menu donné.
	 *
	 * @param integer $iMenuId
	 * @return boolean
	 */
	public function switchMenuStatus($iMenuId)
	{
		if (!$this->menuExists($iMenuId)) {
			throw new \Exception(sprintf(__('c_a_config_navigation_menu_%s_not_exists'), $iMenuId));
		}

		$query =
		'UPDATE '.$this->t_menus.' SET '.
			'active = 1-active '.
		'WHERE id='.(integer)$iMenuId;

		if (!$this->db->execute($query)) {
			throw new \Exception('Unable to update menu into database.');
		}

		return true;
	}

	/**
	 * Suppression d'un menu donné.
	 *
	 * @param integer $iMenuId
	 * @return boolean
	 */
	public function delMenu($iMenuId)
	{
		if (!$this->menuExists($iMenuId)) {
			throw new \Exception(sprintf(__('c_a_config_navigation_menu_%s_not_exists'), $iMenuId));
		}

		# first, remove items
		$rsItems = $this->getItems(array(
			'menu_id' => $iMenuId,
			'active' => 2
		));

		while ($rsItems->fetch()) {
			$this->delItem($rsItems->id);
		}

		# then, remove menu
		$sQuery =
		'DELETE FROM '.$this->t_menus.' '.
		'WHERE id='.(integer)$iMenuId;

		if (!$this->db->execute($sQuery)) {
			throw new \Exception('Unable to delete menu from database.');
		}

		$this->db->optimize($this->t_menus);

		return true;
	}

	/**
	 * Créer une instance de cursor pour les menus et la retourne.
	 *
	 * @param array $aData
	 * @return object cursor
	 */
	protected function openMenuCursor(array $aData=array())
	{
		$oCursor = $this->db->openCursor($this->t_menus);

		if (!empty($aData))
		{
			foreach ($aData as $k=>$v) {
				$oCursor->$k = $v;
			}
		}

		return $oCursor;
	}

	/**
	 * Retourne une liste d'éléments sous forme de recordset.
	 *
	 * @param array $aParams
	 * @return Recordset
	 */
	public function getItems(array $aParams=array())
	{
		$sReqPlus = '';

		if (!empty($aParams['id'])) {
			$sReqPlus .= ' AND i.id='.(integer)$aParams['id'].' ';
		}

		if (!empty($aParams['menu_id'])) {
			$sReqPlus .= ' AND i.menu_id='.(integer)$aParams['menu_id'].' ';
		}

		if (!empty($aParams['language'])) {
			$sReqPlus .= 'AND il.language=\''.$this->db->escapeStr($aParams['language']).'\' ';
		}

		if (isset($aParams['active']))
		{
			if ($aParams['active'] == 0) {
				$sReqPlus .= 'AND i.active=0 ';
			}
			elseif ($aParams['active'] == 1) {
				$sReqPlus .= 'AND i.active=1 ';
			}
			elseif ($aParams['active'] == 2) {
				$sReqPlus .= '';
			}
		}
		else {
			$sReqPlus .= 'AND i.active=1 ';
		}

		$sQuery =
		'SELECT i.id, i.menu_id, i.active, i.type, i.ord, il.title, il.url '.
		'FROM '.$this->t_items.' AS i '.
			'LEFT JOIN '.$this->t_items_locales.' AS il ON i.id=il.item_id '.
		'WHERE 1 '.
		$sReqPlus;

		if (!empty($aParams['order'])) {
			$sQuery .= 'ORDER BY '.$aParams['order'].' ';
		}
		else {
			$sQuery .= 'ORDER BY i.ord ASC ';
		}

		if (($rs = $this->db->select($sQuery, 'Okatea\Tao\Navigation\Menus\ItemsRecordset')) === false) {
			$rs = new ItemsRecordset(array());
		}

		$rs->setCore($this->okt);
		return $rs;
	}

	/**
	 * Retourne un élément donné sous forme de recordset.
	 *
	 * @param integer $iItemId
	 * @param integer $iActive
	 * @return object recordset
	 */
	public function getItem($iItemId, $iActive=2)
	{
		return $this->getItems(array(
			'id' => $iItemId,
			'active' => $iActive
		));
	}

	/**
	 * Indique si un élément donné existe.
	 *
	 * @param $iItemId
	 * @return boolean
	 */
	public function itemExists($iItemId)
	{
		if (empty($iItemId) || $this->getItem($iItemId)->isEmpty()) {
			return false;
		}

		return true;
	}

	/**
	 * Retourne les localisations d'un élément donné.
	 *
	 * @param integer $iItemId
	 * @return Recordset
	 */
	public function getItemL10n($iItemId)
	{
		$query =
		'SELECT * FROM '.$this->t_items_locales.' '.
		'WHERE item_id='.(integer)$iItemId;

		if (($rs = $this->db->select($query)) === false) {
			$rs = new Recordset(array());
			return $rs;
		}

		return $rs;
	}

	/**
	 * Ajout d'un élément.
	 *
	 * @param array $aData
	 * @return integer
	 */
	public function addItem($aData)
	{
		$oCursor = $this->openItemCursor($aData['item']);

		$sQuery = 'SELECT MAX(ord) FROM '.$this->t_items;
		$rs = $this->db->select($sQuery);

		if ($rs->isEmpty()) {
			throw new \Exception('Unable to retrieve max ord from database.');
		}

		$max_ord = $rs->f(0);
		$oCursor->ord = (integer)($max_ord+1);

		if (!$oCursor->insert()) {
			throw new \Exception('Unable to insert item into database.');
		}

		$iItemId = $this->db->getLastID();

		$this->setItemL10n($iItemId, $aData['locales']);

		return $iItemId;
	}

	/**
	 * Modification d'un élément.
	 *
	 * @param array $aData
	 * @return integer
	 */
	public function updItem($aData)
	{
		if (!$this->itemExists($aData['item']['id'])) {
			throw new \Exception(sprintf(__('c_a_config_navigation_item_%s_not_exists'), $aData['item']['id']));
		}

		$oCursor = $this->openItemCursor($aData['item']);

		if (!$oCursor->update('WHERE id='.(integer)$aData['item']['id'])) {
			throw new \Exception('Unable to update item into database.');
		}

		$this->setItemL10n($aData['item']['id'], $aData['locales']);

		return true;
	}

	/**
	 * Vérifie les données envoyées en POST pour l'ajout ou la modification d'un élément.
	 *
	 * @param array $aData
	 * @return boolean
	 */
	public function checkPostItemData($aData)
	{
		foreach ($this->okt->languages->list as $aLanguage)
		{
			if (empty($aData['locales'][$aLanguage['code']]['title']))
			{
				if ($this->okt->languages->unique) {
					$this->error->set(__('c_a_config_navigation_must_enter_title'));
				}
				else {
					$this->error->set(sprintf(__('c_a_config_navigation_must_enter_title_in_%s'), $aLanguage['title']));
				}
			}
/*
			if (empty($aData['locales'][$aLanguage['code']]['url']))
			{
				if ($this->okt->languages->unique) {
					$this->error->set(__('c_a_config_navigation_must_enter_url'));
				}
				else {
					$this->error->set(sprintf(__('c_a_config_navigation_must_enter_url_in_%s'), $aLanguage['title']));
				}
			}
*/		}

		return $this->error->isEmpty();
	}

	/**
	 * Suppression d'un élément donné.
	 *
	 * @param integer $iItemId
	 * @return boolean
	 */
	public function delItem($iItemId)
	{
		if (!$this->itemExists($iItemId)) {
			throw new \Exception(sprintf(__('c_a_config_navigation_item_%s_not_exists'), $iItemId));
		}

		$sQuery =
		'DELETE FROM '.$this->t_items.' '.
		'WHERE id='.(integer)$iItemId;

		if (!$this->db->execute($sQuery)) {
			throw new \Exception('Unable to delete item from database.');
		}

		$this->db->optimize($this->t_items);

		$sQuery =
		'DELETE FROM '.$this->t_items_locales.' '.
		'WHERE item_id='.(integer)$iItemId;

		if (!$this->db->execute($sQuery)) {
			throw new \Exception('Unable to delete item locales from database.');
		}

		$this->db->optimize($this->t_items_locales);

		return true;
	}

	/**
	 * Définit le statut d'un élément donné.
	 *
	 * @param integer $iItemId
	 * @param integer $iStatus
	 * @return boolean
	 */
	public function setItemStatus($iItemId, $iStatus)
	{
		if (!$this->itemExists($iItemId)) {
			throw new \Exception(sprintf(__('c_a_config_navigation_item_%s_not_exists'), $iItemId));
		}

		$iStatus = ($iStatus == 1) ? 1 : 0;

		$query =
		'UPDATE '.$this->t_items.' SET '.
		'active = '.$iStatus.' '.
		'WHERE id='.(integer)$iItemId;

		if (!$this->db->execute($query)) {
			throw new \Exception('Unable to update item in database.');
		}

		return true;
	}

	/**
	 * Met à jour la position d'un élément donné.
	 *
	 * @param integer $iItemId
	 * @param integer $iPosition
	 * @return boolean
	 */
	public function updItemOrder($iItemId, $iPosition)
	{
		if (!$this->itemExists($iItemId)) {
			throw new \Exception(sprintf(__('c_a_config_navigation_item_%s_not_exists'), $iItemId));
		}

		$query =
		'UPDATE '.$this->t_items.' SET '.
		'ord='.(integer)$iPosition.' '.
		'WHERE id='.(integer)$iItemId;

		if (!$this->db->execute($query)) {
			throw new \Exception('Unable to update item in database.');
		}

		return true;
	}

	/**
	 * Ajout/modification des textes internationnalisés de l'élément.
	 *
	 * @param integer $iItemId
	 * @param array $aData
	 */
	protected function setItemL10n($iItemId, $aData)
	{
		foreach ($this->okt->languages->list as $aLanguage)
		{
			$oCursor = $this->db->openCursor($this->t_items_locales);

			$oCursor->item_id = $iItemId;

			$oCursor->language = $aLanguage['code'];

			foreach ($aData[$aLanguage['code']] as $k=>$v) {
				$oCursor->$k = $v;
			}

			if (!$oCursor->insertUpdate()) {
				throw new \Exception('Unable to insert item locales in database for '.$aLanguage['code'].' language.');
			}
		}
	}

	/**
	 * Créer une instance de cursor pour les éléments et la retourne.
	 *
	 * @param array $aData
	 * @return object cursor
	 */
	protected function openItemCursor(array $aData=array())
	{
		$oCursor = $this->db->openCursor($this->t_items);

		if (!empty($aData))
		{
			foreach ($aData as $k=>$v) {
				$oCursor->$k = $v;
			}
		}

		return $oCursor;
	}
}
