<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Navigation\Menus;

use Okatea\Tao\Application;
use Okatea\Tao\Misc\Utilities;

/**
 * Navigation menus manager.
 */
class Menus
{
	/**
	 * Okatea application instance.
	 *
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * The name of the menu table.
	 *
	 * @var string
	 */
	protected $sMenusTable;

	/**
	 * The name of the menu items table.
	 *
	 * @var string
	 */
	protected $sItemsTable;

	/**
	 * The name of the locales menu items table.
	 *
	 * @var string
	 */
	protected $sItemsLocalesTable;

	/**
	 * Constructor.
	 *
	 * @param Okatea\Tao\Application $okt Okatea application instance.
	 * @return void
	 */
	public function __construct(Application $okt)
	{
		$this->okt = $okt;

		$this->sMenusTable = $okt['config']->database_prefix . 'core_nav_menus';
		$this->sItemsTable = $okt['config']->database_prefix . 'core_nav_items';
		$this->sItemsLocalesTable = $okt['config']->database_prefix . 'core_nav_items_locales';
	}

	/**
	 * Returns a list of menus ​​according to given parameters.
	 *
	 * @param array $aParams
	 * @return Recordset
	 */
	public function getMenus(array $aParams = [])
	{
		$queryBuilder = $this->okt['db']->createQueryBuilder();

		$queryBuilder
			->select('m.id', 'm.title', 'm.active', 'm.tpl', 'COUNT(i.id) AS num_items')
			->from($this->sMenusTable, 'm')
			->leftJoin('m', $this->sItemsTable, 'i', 'm.id = i.menu_id')
			->where('true = true')
			->groupBy('m.id');

		if (!empty($aParams['id']))
		{
			$queryBuilder
				->andWhere('m.id = :id')
				->setParameter('id', (integer)$aParams['id']);
		}

		if (!empty($aParams['title']))
		{
			$queryBuilder
				->andWhere('m.title = :title')
				->setParameter('title', $aParams['title']);
		}

		if (!isset($aParams['active'])) {
			$queryBuilder->andWhere('m.active = 1');
		}
		elseif ($aParams['active'] == 0) {
			$queryBuilder->andWhere('m.active = 0');
		}
		elseif ($aParams['active'] == 1) {
			$queryBuilder->andWhere('m.active = 1');
		}

		return $queryBuilder->execute()->fetchAll();
	}

	/**
	 * Returns information of a given menu.
	 *
	 * @param integer $iMenuId
	 * @param integer $iActive
	 * @return Recordset
	 */
	public function getMenu($iMenuId, $iActive = 2)
	{
		$aMenu = $this->getMenus([
			'id' 		=> $iMenuId,
			'active' 	=> $iActive
		]);

		return isset($aMenu[0]) ? $aMenu[0] : null;
	}

	/**
	 * Indicates whether a given menu exists.
	 *
	 * @param integer $iMenuId
	 * @return boolean
	 */
	public function menuExists($iMenuId)
	{
		return $this->getMenu($iMenuId) ? true : false;
	}

	/**
	 * Adding a menu.
	 *
	 * @param array $aData
	 * @return integer
	 */
	public function addMenu($aData)
	{
		$this->okt['db']->insert($this->sMenusTable, $aData);

		return $this->okt['db']->lastInsertId();
	}

	/**
	 * Update a menu.
	 *
	 * @param integer $iMenuId
	 * @param array $aData
	 * @return boolean
	 */
	public function updMenu($iMenuId, $aData)
	{
		if (!$this->menuExists($iMenuId)) {
			$this->okt['instantMessage']->error(sprintf(__('c_a_config_navigation_menu_%s_not_exists'), $iMenuId));
		}

		$this->okt['db']->update($this->sMenusTable, $aData, ['id' => (integer) $iMenuId]);

		return true;
	}

	/**
	 * Check the POST data sent to adding or editing a menu.
	 *
	 * @param array $aData
	 * @return boolean
	 */
	public function checkPostMenuData($aData)
	{
		if (empty($aData['title'])) {
			$this->okt['instantMessages']->error(__('c_a_config_navigation_must_enter_title'));
		}

		return !$this->okt['instantMessages']->hasError();
	}

	/**
	 * Switch the visibility status of a given menu.
	 *
	 * @param integer $iMenuId
	 * @return boolean
	 */
	public function switchMenuStatus($iMenuId)
	{
		if (!$this->menuExists($iMenuId)) {
			$this->okt['instantMessage']->error(sprintf(__('c_a_config_navigation_menu_%s_not_exists'), $iMenuId));
		}

		$this->okt['db']->update($this->sMenusTable, ['active' => '1-active'], ['id' => (integer) $iMenuId]);

		return true;
	}

	/**
	 * Deleting a given menu.
	 *
	 * @param integer $iMenuId
	 * @return boolean
	 */
	public function delMenu($iMenuId)
	{
		if (!$this->menuExists($iMenuId)) {
			$this->okt['instantMessage']->error(sprintf(__('c_a_config_navigation_menu_%s_not_exists'), $iMenuId));
		}

		# first, remove items
		$aItems = $this->okt['menusItems']->getItems([
			'menu_id' 	=> $iMenuId,
			'active' 	=> 2
		]);

		foreach ($aItems as $aItem) {
			$this->okt['menusItems']->delItem($aItem['id']);
		}

		# then, remove menu
		$this->okt['db']->delete($this->sMenusTable, array('id' => (integer) $iMenuId));

		return true;
	}

	/**
	 * Render a given menu.
	 *
	 * @param mixed $mMenu
	 * @param string $sUserTpl
	 * @return string
	 */
	public function render($mMenu, $sUserTpl = null)
	{
		# menu data
		$aMenuParams = [
			'language' 	=> $this->okt['visitor']->language,
			'active' 	=> 1
		];

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

		# items data
		$rsItems = $this->okt['menusItems']->getItems([
			'menu_id' 	=> $rsMenu->id,
			'language' 	=> $this->okt['visitor']->language,
			'active' 	=> 1
		]);

		# render template
		$sTemplate = $this->okt['config']->navigation_tpl['default'];

		if (!empty($sUserTpl) && in_array($sUserTpl, $this->okt['config']->navigation_tpl['usables'])) {
			$sTemplate = $sUserTpl;
		}
		elseif (!empty($rsMenu['tpl']) && in_array($rsMenu['tpl'], $this->okt['config']->navigation_tpl['usables'])) {
			$sTemplate = $rsMenu['tpl'];
		}

		return $this->okt['tpl']->render('navigation/' . $sTemplate . '/template', [
			'rsMenu' 	=> $rsMenu,
			'rsItems' 	=> $rsItems
		]);
	}
}
