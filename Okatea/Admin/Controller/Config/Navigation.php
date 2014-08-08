<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Admin\Controller\Config;

use ArrayObject;
use Okatea\Admin\Controller;
use Okatea\Tao\Themes\TemplatesSet;

class Navigation extends Controller
{
	public function page()
	{
		if (!$this->okt['visitor']->checkPerm('is_superadmin')) {
			return $this->serve401();
		}

		# locales
		$this->okt['l10n']->loadFile($this->okt['locales_path'] . '/%s/admin/navigation');

		# titre et fil d'ariane
		$this->page->addGlobalTitle(__('c_a_config_navigation'), $this->generateUrl('config_navigation'));

		$sDo = $this->okt['request']->query->get('do');

		if (!$sDo || $sDo === 'index') {
			return $this->index();
		}
		elseif ($sDo === 'menu') {
			return $this->menu();
		}
		elseif ($sDo === 'items') {
			return $this->items();
		}
		elseif ($sDo === 'item') {
			return $this->item();
		}
		elseif ($sDo === 'config') {
			return $this->config();
		}
		else {
			return $this->serve404();
		}
	}

	protected function index()
	{
		# disable a menu
		$iMenuIdDisable = $this->okt['request']->query->getInt('disable');

		if ($iMenuIdDisable)
		{
			try
			{
				$this->okt['menus']->disableMenu($iMenuIdDisable);

				$this->okt['flashMessages']->success(__('c_a_config_navigation_menu_switched'));

				return $this->redirect($this->generateUrl('config_navigation'));
			}
			catch (\Exception $e) {
				$this->okt['instantMessages']->error($e->getMessage());
			}
		}

		# enable a menu
		$iMenuIdEnalble = $this->okt['request']->query->getInt('enable');

		if ($iMenuIdEnalble)
		{
			try
			{
				$this->okt['menus']->enableMenu($iMenuIdEnalble);

				$this->okt['flashMessages']->success(__('c_a_config_navigation_menu_switched'));

				return $this->redirect($this->generateUrl('config_navigation'));
			}
			catch (\Exception $e) {
				$this->okt['instantMessages']->error($e->getMessage());
			}
		}

		# delete a menu
		$iMenuIdDelete = $this->okt['request']->query->getInt('delete_menu');
		if ($iMenuIdDelete)
		{
			try
			{
				$this->okt['menus']->delMenu($iMenuIdDelete);

				$this->okt['flashMessages']->success(__('c_a_config_navigation_menu_deleted'));

				return $this->redirect($this->generateUrl('config_navigation') . '?do=index');
			}
			catch (\Exception $e) {
				$this->okt['instantMessages']->error($e->getMessage());
			}
		}

		$aMenus = $this->okt['menus']->getMenus([
			'active' => 2
		]);

		foreach ($aMenus as $i=>$aMenu)
		{
			if ($aMenu['num_items'] > 0)
			{
				$aMenus[$i]['items'] = $this->okt['menusItems']->getItems([
					'menu_id' 	=> $aMenu['id'],
					'language' 	=> $this->okt['visitor']->language,
					'active' 	=> 2
				]);
			}
		}

		return $this->render('Config/Navigation/Index', [
			'aMenus' => $aMenus
		]);
	}

	protected function menu()
	{
		$iMenuId = null;

		$aMenuData = [
			'title'      => '',
			'active'     => 1,
			'tpl'        => ''
		];

		# menu update ?
		$iMenuId = $this->okt['request']->query->getInt('menu_id', $this->okt['request']->request->getInt('menu_id'));

		if ($iMenuId)
		{
			$aMenu = $this->okt['menus']->getMenu($iMenuId);

			if (empty($aMenu))
			{
				$this->okt['instantMessages']->error(sprintf(__('c_a_config_navigation_menu_%s_not_exists'), $iMenuId));
				$iMenuId = null;
			}
			else
			{
				$aMenuData = [
					'title' 	=> $aMenu['title'],
					'active' 	=> $aMenu['active'],
					'tpl' 		=> $aMenu['tpl']
				];
			}
		}

		# add/update a menu
		if ($this->okt['request']->request->has('sended'))
		{
			$aMenuData = [
				'title' 	=> $this->okt['request']->request->get('p_title', ''),
				'active' 	=> $this->okt['request']->request->has('p_active') ? 1 : 0,
				'tpl' 		=> $this->okt['request']->request->get('p_tpl', '')
			];

			# update menu
			if (!empty($iMenuId))
			{
				if ($this->okt['menus']->checkPostMenuData($aMenuData) !== false)
				{
					try
					{
						$this->okt['menus']->updMenu($iMenuId, $aMenuData);

						# log admin
						$this->okt['logAdmin']->info([
							'code' 			=> 41,
							'component' 	=> 'menus',
							'message' 		=> 'menu #' . $iMenuId
						]);

						$this->okt['flashMessages']->success(__('c_a_config_navigation_menu_updated'));

						return $this->redirect($this->generateUrl('config_navigation') . '?do=menu&menu_id=' . $iMenuId);
					}
					catch (\Exception $e) {
						$this->okt['instantMessages']->error($e->getMessage());
					}
				}
			}

			# add menu
			else
			{
				if ($this->okt['menus']->checkPostMenuData($aMenuData) !== false)
				{
					try
					{
						$iMenuId = $this->okt['menus']->addMenu($aMenuData);

						# log admin
						$this->okt['logAdmin']->info([
							'code' 			=> 40,
							'component' 	=> 'menus',
							'message' 		=> 'menu #' . $iMenuId
						]);

						$this->okt['flashMessages']->success(__('c_a_config_navigation_menu_added'));

						return $this->redirect($this->generateUrl('config_navigation') . '?do=menu&menu_id=' . $iMenuId);
					}
					catch (\Exception $e) {
						$this->okt['instantMessages']->error($e->getMessage());
					}
				}
			}
		}

		# Liste des templates utilisables
		$oTemplates = new TemplatesSet($this->okt, $this->okt['config']->navigation_tpl, 'navigation', 'navigation');
		$aTplChoices = array_merge(
			[ '&nbsp;' => null ],
			$oTemplates->getUsablesTemplatesForSelect($this->okt['config']->navigation_tpl['usables'])
		);

		return $this->render('Config/Navigation/Menu', [
			'iMenuId'        => $iMenuId,
			'aMenuData'      => $aMenuData,
			'aTplChoices'    => $aTplChoices
		]);
	}

	protected function items()
	{
		$iMenuId = $this->okt['request']->query->getInt('menu_id', $this->okt['request']->request->getInt('menu_id'));

		$aMenu = $this->okt['menus']->getMenu($iMenuId);

		if (empty($iMenuId) || empty($aMenu)) {
			return $this->redirect($this->generateUrl('config_navigation'));
		}

		# AJAX : changement de l'ordre des éléments
		if ($this->okt['request']->query->has('ajax_update_order'))
		{
			$aItemsOrder = $this->okt['request']->query->get('ord', []);

			if (!empty($aItemsOrder))
			{
				foreach ($aItemsOrder as $ord => $id)
				{
					$ord = ((integer) $ord) + 1;
					$this->okt['menusItems']->updItemOrder($id, $ord);
				}
			}

			exit();
		}

		# POST : changement de l'ordre des éléments
		if ($this->okt['request']->request->has('order_items'))
		{
			try
			{
				$aItemsOrder = $this->okt['request']->query->get('p_order', []);

				asort($aItemsOrder);

				$aItemsOrder = array_keys($aItemsOrder);

				if (!empty($aItemsOrder))
				{
					foreach ($aItemsOrder as $ord => $id)
					{
						$ord = ((integer) $ord) + 1;
						$this->okt['menusItems']->updItemOrder($id, $ord);
					}

					$this->okt['flashMessages']->success(__('c_a_config_navigation_items_neworder'));

					return $this->redirect($this->generateUrl('config_navigation') . '?do=items&menu_id=' . $iMenuId);
				}
			}
			catch (\Exception $e) {
				$this->okt['instantMessages']->error($e->getMessage());
			}
		}

		# activation d'un élément
		$iItemIdEnable = $this->okt['request']->query->getInt('enable');

		if ($iItemIdEnable)
		{
			try
			{
				$this->okt['menusItems']->setItemStatus($iItemIdEnable, 1);

				$this->okt['flashMessages']->success(__('c_a_config_navigation_item_enabled'));

				return $this->redirect($this->generateUrl('config_navigation') . '?do=items&menu_id=' . $iMenuId);
			}
			catch (\Exception $e) {
				$this->okt['instantMessages']->error($e->getMessage());
			}
		}

		# désactivation d'un élément
		$iItemIdDisable = $this->okt['request']->query->getInt('disable');

		if ($iItemIdDisable)
		{
			try
			{
				$this->okt['menusItems']->setItemStatus($iItemIdDisable, 0);

				$this->okt['flashMessages']->success(__('c_a_config_navigation_item_disabled'));

				return $this->redirect($this->generateUrl('config_navigation') . '?do=items&menu_id=' . $iMenuId);
			}
			catch (\Exception $e) {
				$this->okt['instantMessages']->error($e->getMessage());
			}
		}

		# suppression d'un élément
		$iItemIdDelete = $this->okt['request']->query->getInt('delete');

		if ($iItemIdDelete && $this->okt['menusItems']->delItem($iItemIdDelete))
		{
			$this->okt['flashMessages']->success(__('c_a_config_navigation_item_deleted'));

			return $this->redirect($this->generateUrl('config_navigation') . '?do=items&menu_id=' . $iMenuId);
		}

		$aItems = $this->okt['menusItems']->getItems([
			'menu_id' 	=> $iMenuId,
			'language' 	=> $this->okt['visitor']->language,
			'active' 	=> 2
		]);

		return $this->render('Config/Navigation/Items', [
			'iMenuId' 	=> $iMenuId,
			'aMenu' 	=> $aMenu,
			'aItems' 	=> $aItems
		]);
	}

	protected function item()
	{
		$iMenuId =
			$this->okt['request']->query->getInt('menu_id',
				$this->okt['request']->request->getInt('menu_id'));

		$aMenu = $this->okt['menus']->getMenu($iMenuId);

		if (empty($iMenuId) || empty($aMenu)) {
			return $this->redirect($this->generateUrl('config_navigation'));
		}

		# Item data
		$iItemId =
			$this->okt['request']->query->getInt('item_id',
				$this->okt['request']->request->getInt('item_id'));

		$aItemData = new ArrayObject();

		$aItemData['item'] = [];

		$aItemData['item']['menu_id'] = $iMenuId;
		$aItemData['item']['active'] = 1;
		$aItemData['item']['type'] = 0;

		$aItemData['locales'] = [];

		foreach ($this->okt['languages']->getList() as $aLanguage)
		{
			$aItemData['locales'][$aLanguage['code']] = [];

			$aItemData['locales'][$aLanguage['code']]['title'] = '';
			$aItemData['locales'][$aLanguage['code']]['url'] = '';
		}

		# item update ?
		if ($iItemId)
		{
			$aItem = $this->okt['menusItems']->getItem($iItemId);

			if (empty($aItem))
			{
				$this->okt['instantMessages']->error(sprintf(__('c_a_config_navigation_item_%s_not_exists'), $iItemId));
				$iItemId = null;
			}
			else
			{
				$aItemData['item']['menu_id'] = $aItem['menu_id'];
				$aItemData['item']['active'] = $aItem['active'];
				$aItemData['item']['type'] = $aItem['type'];

				$aItemI10n = $this->okt['menusItems']->getItemL10n($iItemId);

				foreach ($this->okt['languages']->getList() as $aLanguage)
				{
					foreach ($aItemI10n as $aL10n)
					{
						if ($aL10n['language'] == $aLanguage['code'])
						{
							$aItemData['locales'][$aLanguage['code']]['title'] = $aL10n['title'];
							$aItemData['locales'][$aLanguage['code']]['url'] = $aL10n['url'];
						}
					}
				}
			}
		}

		#  ajout / modifications d'un élément
		if ($this->okt['request']->request->has('sended'))
		{
			$aItemData['item']['active'] = $this->okt['request']->request->has('p_active') ? 1 : 0;
			$aItemData['item']['type'] = $this->okt['request']->request->has('p_type') ? 1 : 0;

			foreach ($this->okt['languages']->getList() as $aLanguage)
			{
				$aItemData['locales'][$aLanguage['code']]['title'] = $this->okt['request']->request->get('p_title[' . $aLanguage['code'] . ']', '', true);
				$aItemData['locales'][$aLanguage['code']]['url'] = $this->okt['request']->request->get('p_url[' . $aLanguage['code'] . ']', '', true);
			}

			# update item
			if (!empty($iItemId))
			{
				if ($this->okt['menusItems']->checkPostItemData($aItemData) !== false)
				{
					if ($this->okt['menusItems']->updItem($iItemId, $aItemData))
					{
						# log admin
						$this->okt['logAdmin']->info(array(
							'code' 			=> 41,
							'component' 	=> 'menu item',
							'message' 		=> 'item #' . $iItemId
						));

						$this->okt['flashMessages']->success(__('c_a_config_navigation_item_updated'));

						return $this->redirect($this->generateUrl('config_navigation') . '?do=item&menu_id=' . $iMenuId . '&item_id=' . $iItemId);
					}
				}
			}
			# add item
			else
			{
				if ($this->okt['menusItems']->checkPostItemData($aItemData) !== false)
				{
					try
					{
						$iItemId = $this->okt['menusItems']->addItem($aItemData);

						# log admin
						$this->okt['logAdmin']->info([
							'code'       => 40,
							'component'  => 'menu item',
							'message'    => 'item #' . $iItemId
						]);

						$this->okt['flashMessages']->success(__('c_a_config_navigation_item_added'));

						return $this->redirect($this->generateUrl('config_navigation') . '?do=item&menu_id=' . $iMenuId . '&item_id=' . $iItemId);
					}
					catch (\Exception $e) {
						$this->okt['instantMessages']->error($e->getMessage());
					}
				}
			}
		}

		return $this->render('Config/Navigation/Item', [
			'iMenuId'    => $iMenuId,
			'iItemId'    => $iItemId,
			'aMenu'      => $aMenu,
			'aItemData'  => $aItemData
		]);
	}

	protected function config()
	{
		$oTemplates = new TemplatesSet($this->okt, $this->okt['config']->navigation_tpl, 'navigation', 'navigation', $this->generateUrl('config_navigation') . '?do=config&amp;');

		if ($this->okt['request']->request->has('sended'))
		{
			$p_tpl = $oTemplates->getPostConfig();

			if (!$this->okt['flashMessages']->hasError())
			{
				$this->okt['config']->write([
					'navigation_tpl' => $p_tpl
				]);

				$this->okt['flashMessages']->success(__('c_c_confirm_configuration_updated'));

				return $this->redirect($this->generateUrl('config_navigation') . '?do=config');
			}
		}

		return $this->render('Config/Navigation/Config', [
			'oTemplates' => $oTemplates
		]);
	}
}
