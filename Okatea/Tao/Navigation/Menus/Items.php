<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Navigation\Menus;

use Okatea\Tao\Misc\Utilities;

/**
 * Le gestionnnaire des éléments des menus de navigation.
 */
class Items
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
	public function __construct($okt)
	{
		$this->okt = $okt;

		$this->sMenusTable = $okt['config']->database_prefix . 'core_nav_menus';
		$this->sItemsTable = $okt['config']->database_prefix . 'core_nav_items';
		$this->sItemsLocalesTable = $okt['config']->database_prefix . 'core_nav_items_locales';
	}

	/**
	 * Returns a list of items ​​according to given parameters.
	 *
	 * @param array $aParams
	 * @return Recordset
	 */
	public function getItems(array $aParams = [])
	{
		$queryBuilder = $this->okt['db']->createQueryBuilder();

		$queryBuilder
			->select('i.id', 'i.menu_id', 'i.active', 'i.type', 'i.ord', 'il.title', 'il.url')
			->from($this->sItemsTable, 'i')
			->leftJoin('i', $this->sItemsLocalesTable, 'il', 'i.id = il.item_id')
			->where('true = true');

		if (!empty($aParams['id']))
		{
			$queryBuilder
				->andWhere('i.id = :id')
				->setParameter('id', (integer)$aParams['id']);
		}

		if (!empty($aParams['menu_id']))
		{
			$queryBuilder
				->andWhere('i.menu_id = :menu_id')
				->setParameter('menu_id', (integer)$aParams['menu_id']);
		}

		if (!empty($aParams['language']))
		{
			$queryBuilder
				->andWhere('il.language = :language')
				->setParameter('language', $aParams['language']);
		}

		if (!isset($aParams['active'])) {
			$queryBuilder->andWhere('i.active = 1');
		}
		elseif ($aParams['active'] == 0) {
			$queryBuilder->andWhere('i.active = 0');
		}
		elseif ($aParams['active'] == 1) {
			$queryBuilder->andWhere('i.active = 1');
		}

		if (!empty($aParams['order']) && !empty($aParams['order_direction'])) {
			$queryBuilder->orderBy($aParams['order'], $aParams['order_direction']);
		}
		else {
			$queryBuilder->orderBy('i.ord', 'ASC');
		}

		return $queryBuilder->execute()->fetchAll();
	}

	/**
	 * Returns information of a given item.
	 *
	 * @param integer $iItemId
	 * @param integer $iActive
	 * @return object recordset
	 */
	public function getItem($iItemId, $iActive = 2)
	{
		$aItem = $this->getItems([
			'id' 		=> $iItemId,
			'active' 	=> $iActive
		]);

		return isset($aItem[0]) ? $aItem[0] : null;
	}

	/**
	 * Indicates whether a given item exists.
	 *
	 * @param integer $iItemId
	 * @return boolean
	 */
	public function itemExists($iItemId)
	{
		return $this->getItem($iItemId) ? true : false;
	}

	/**
	 * Returns the internationalized data of a given item.
	 *
	 * @param integer $iItemId
	 * @return Recordset
	 */
	public function getItemL10n($iItemId)
	{
		$queryBuilder = $this->okt['db']->createQueryBuilder();

		$queryBuilder
			->select('*')
			->from($this->sItemsLocalesTable)
			->where('item_id = :item_id')
			->setParameter('item_id', (integer) $iItemId)
		;

		return $queryBuilder->execute()->fetchAll();
	}

	/**
	 * Indicates whether the internationalized data for a given item and a given language exist.
	 *
	 * @param integer $iItemId
	 * @param string $sLanguage
	 * @return boolean
	 */
	public function itemL10nExists($iItemId, $sLanguage)
	{
		$queryBuilder = $this->okt['db']->createQueryBuilder();

		$queryBuilder
			->select('COUNT(item_id)')
			->from($this->sItemsLocalesTable)
			->where('item_id = :item_id')
			->andWhere('language = :language')
			->setParameter('item_id', (integer) $iItemId)
			->setParameter('language', $sLanguage)
		;

		$iNumRow = (integer) $queryBuilder->execute()->fetchColumn();

		return $iNumRow >= 1;
	}

	/**
	 * Add an item.
	 *
	 * @param array $aData
	 * @return integer
	 */
	public function addItem($aData)
	{
		$iMaxOrd = (integer) $this->okt['db']->fetchColumn('SELECT MAX(ord) FROM ' . $this->sItemsTable);
		$aData['item']['ord'] = $iMaxOrd + 1;

		$this->okt['db']->insert($this->sItemsTable, $aData['item']);

		$iItemId = $this->okt['db']->lastInsertId();

		$this->setItemL10n($iItemId, $aData['locales']);

		return $iItemId;
	}

	/**
	 * Update an item
	 *
	 * @param array $aData
	 * @return integer
	 */
	public function updItem($iItemId, $aData)
	{
		if (!$this->itemExists($iItemId)) {
			$this->okt['instantMessages']->error(__('c_a_config_navigation_item_%s_not_exists'), $iItemId);
			return false;
		}

		$this->okt['db']->update(
			$this->sItemsTable,
			$aData['item'],
			[ 'id' => (integer) $iItemId ]
		);

		$this->setItemL10n($iItemId, $aData['locales']);

		return true;
	}

	/**
	 * Check the POST data sent to adding or editing an item menu.
	 *
	 * @param array $aData
	 * @return boolean
	 */
	public function checkPostItemData($aData)
	{
		foreach ($this->okt['languages']->getList() as $aLanguage)
		{
			if (empty($aData['locales'][$aLanguage['code']]['title']))
			{
				if ($this->okt['languages']->hasUniqueLanguage()) {
					$this->okt['instantMessages']->error(__('c_a_config_navigation_must_enter_title'));
				}
				else {
					$this->okt['instantMessages']->error(sprintf(__('c_a_config_navigation_must_enter_title_in_%s'), $aLanguage['title']));
				}
			}
			/*
			if (empty($aData['locales'][$aLanguage['code']]['url']))
			{
				if ($this->okt['languages']->hasUniqueLanguage()) {
					$this->okt['flashMessages']->error(__('c_a_config_navigation_must_enter_url'));
				}
				else {
					$this->okt['flashMessages']->error(sprintf(__('c_a_config_navigation_must_enter_url_in_%s'), $aLanguage['title']));
				}
			}
*/
		}

		return !$this->okt['instantMessages']->hasError();
	}

	/**
	 * Deleting a given item.
	 *
	 * @param integer $iItemId
	 * @return boolean
	 */
	public function delItem($iItemId)
	{
		if (!$this->itemExists($iItemId)) {
			$this->okt['instantMessages']->error(__('c_a_config_navigation_item_%s_not_exists'), $iItemId);
			return false;
		}

		$this->okt['db']->delete($this->sItemsLocalesTable, [ 'item_id' => (integer) $iItemId ]);

		$this->okt['db']->delete($this->sItemsTable, [ 'id' => (integer) $iItemId ]);

		return true;
	}

	/**
	 * Sets the status of a given item.
	 *
	 * @param integer $iItemId
	 * @param integer $iStatus
	 * @return boolean
	 */
	public function setItemStatus($iItemId, $iStatus)
	{
		if (!$this->itemExists($iItemId)) {
			$this->okt['instantMessages']->error(__('c_a_config_navigation_item_%s_not_exists'), $iItemId);
			return false;
		}

		$iStatus = ($iStatus == 1) ? 1 : 0;

		$this->okt['db']->update(
			$this->sItemsTable,
			['active' => $iStatus],
			['id' => $iItemId]
		);

		return true;
	}

	/**
	 * Updates the position of a given item.
	 *
	 * @param integer $iItemId
	 * @param integer $iPosition
	 * @return boolean
	 */
	public function updItemOrder($iItemId, $iPosition)
	{
		if (!$this->itemExists($iItemId)) {
			$this->okt['instantMessages']->error(__('c_a_config_navigation_item_%s_not_exists'), $iItemId);
			return false;
		}

		$this->okt['db']->update(
			$this->sItemsTable,
			['ord' => (integer) $iPosition],
			['id' => $iItemId]
		);

		return true;
	}

	/**
	 * Add/Edit internationalized data of a given item.
	 *
	 * @param integer $iItemId
	 * @param array $aData
	 */
	protected function setItemL10n($iItemId, $aData)
	{
		foreach ($this->okt['languages']->getList() as $aLanguage)
		{
			if ($this->itemL10nExists($iItemId, $aLanguage['code']))
			{
				$this->okt['db']->update($this->sItemsLocalesTable,
					$aData[$aLanguage['code']],
					[
						'item_id' => (integer)$iItemId,
						'language' => $aLanguage['code']
					]
				);
			}
			else
			{
				$aData[$aLanguage['code']]['item_id'] = $iItemId;
				$aData[$aLanguage['code']]['language'] = $aLanguage['code'];

				$this->okt['db']->insert($this->sItemsLocalesTable, $aData[$aLanguage['code']]);
			}
		}
	}
}
