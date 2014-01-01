<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Admin\Controller\Config;

use Tao\Admin\Controller;
use Tao\Themes\TemplatesSet;

class Navigation extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('is_superadmin')) {
			return $this->serve401();
		}

		# locales
		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/'.$this->okt->user->language.'/admin.navigation');

		# titre et fil d'ariane
		$this->page->addGlobalTitle(__('c_a_config_navigation'), $this->generateUrl('config_navigation'));

		if (!$this->page->do || $this->page->do === 'index') {
			return $this->index();
		}
		elseif ($this->page->do === 'menu') {
			return $this->menu();
		}
		elseif ($this->page->do === 'items') {
			return $this->items();
		}
		elseif ($this->page->do === 'item') {
			return $this->item();
		}
		elseif ($this->page->do === 'config') {
			return $this->config();
		}
		else {
			return $this->serve404();
		}
	}

	protected function index()
	{
		# switch statut
		if (!empty($_GET['switch_status']))
		{
			try
			{
				$this->okt->navigation->switchMenuStatus($_GET['switch_status']);

				$this->page->flash->success(__('c_a_config_navigation_menu_switched'));

				return $this->redirect($this->generateUrl('config_navigation').'?do=index');
			}
			catch (Exception $e) {
				$this->okt->error->set($e->getMessage());
			}
		}

		# suppression d'un menu
		if (!empty($_GET['delete_menu']))
		{
			try
			{
				$this->okt->navigation->delMenu($_GET['delete_menu']);

				$this->page->flash->success(__('c_a_config_navigation_menu_deleted'));

				return $this->redirect($this->generateUrl('config_navigation').'?do=index');
			}
			catch (Exception $e) {
				$this->okt->error->set($e->getMessage());
			}
		}

		return $this->render('Config/Navigation/Index', array(

		));
	}

	protected function menu()
	{
		$iMenuId = null;

		$aMenuData = array(
			'title' => '',
			'active' => 1,
			'tpl' => ''
		);

		# menu update ?
		if (!empty($_REQUEST['menu_id']))
		{
			$iMenuId = intval($_REQUEST['menu_id']);

			$rsMenu = $this->okt->navigation->getMenu($iMenuId);

			if ($rsMenu->isEmpty())
			{
				$this->okt->error->set(sprintf(__('c_a_config_navigation_menu_%s_not_exists'), $iMenuId));
				$iMenuId = null;
			}
			else
			{
				$aMenuData = array(
					'title' => $rsMenu->title,
					'active' => $rsMenu->active,
					'tpl' => $rsMenu->tpl
				);
			}
		}

		# add/update a menu
		if (!empty($_POST['sended']))
		{
			$aMenuData = array(
				'title' => !empty($_POST['p_title']) ? $_POST['p_title'] : '',
				'active' => !empty($_POST['p_active']) ? 1 : 0,
				'tpl' => !empty($_POST['p_tpl']) ? $_POST['p_tpl'] : ''
			);

			# update menu
			if (!empty($iMenuId))
			{
				$aMenuData['id'] = $iMenuId;

				if ($this->okt->navigation->checkPostMenuData($aMenuData) !== false)
				{
					try
					{
						$this->okt->navigation->updMenu($aMenuData);

						# log admin
						$this->okt->logAdmin->info(array(
						'code' => 41,
						'component' => 'menus',
						'message' => 'menu #'.$iMenuId
						));

						$this->page->flash->success(__('c_a_config_navigation_menu_updated'));

						return $this->redirect($this->generateUrl('config_navigation').'?do=menu&menu_id='.$iMenuId);
					}
					catch (Exception $e) {
						$this->okt->error->set($e->getMessage());
					}
				}
			}

			# add menu
			else
			{
				if ($this->okt->navigation->checkPostMenuData($aMenuData) !== false)
				{
					try
					{
						$iMenuId = $this->okt->navigation->addMenu($aMenuData);

						# log admin
						$this->okt->logAdmin->info(array(
						'code' => 40,
						'component' => 'menus',
						'message' => 'menu #'.$iMenuId
						));

						$this->page->flash->success(__('c_a_config_navigation_menu_added'));

						return $this->redirect($this->generateUrl('config_navigation').'?do=menu&menu_id='.$iMenuId);
					}
					catch (Exception $e) {
						$this->okt->error->set($e->getMessage());
					}
				}
			}
		}

		return $this->render('Config/Navigation/Menu', array(
			'iMenuId' => $iMenuId,
			'aMenuData' => $aMenuData
		));
	}

	protected function items()
	{
		$iMenuId = !empty($_REQUEST['menu_id']) ? intval($_REQUEST['menu_id']) : null;

		$rsMenu = $this->okt->navigation->getMenu($iMenuId);

		if (empty($iMenuId) || $rsMenu->isEmpty()) {
			return $this->redirect($this->generateUrl('config_navigation'));
		}

		# AJAX : changement de l'ordre des éléments
		if (!empty($_GET['ajax_update_order']))
		{
			$aItemsOrder = !empty($_GET['ord']) && is_array($_GET['ord']) ? $_GET['ord'] : array();

			if (!empty($aItemsOrder))
			{
				foreach ($aItemsOrder as $ord=>$id)
				{
					$ord = ((integer)$ord)+1;
					$this->okt->navigation->updItemOrder($id, $ord);
				}
			}

			exit();
		}

		# POST : changement de l'ordre des langues
		if (!empty($_POST['order_items']))
		{
			try
			{
				$aItemsOrder = !empty($_POST['p_order']) && is_array($_POST['p_order']) ? $_POST['p_order'] : array();

				asort($aItemsOrder);

				$aItemsOrder = array_keys($aItemsOrder);

				if (!empty($aItemsOrder))
				{
					foreach ($aItemsOrder as $ord=>$id)
					{
						$ord = ((integer)$ord)+1;
						$this->okt->navigation->updItemOrder($id, $ord);
					}

					$this->page->flash->success(__('c_a_config_navigation_items_neworder'));

					return $this->redirect($this->generateUrl('config_navigation').'?do=items&menu_id='.$iMenuId);
				}
			}
			catch (Exception $e) {
				$this->okt->error->set($e->getMessage());
			}
		}

		# activation d'un élément
		if (!empty($_GET['enable']))
		{
			try
			{
				$this->okt->navigation->setItemStatus($_GET['enable'], 1);

				$this->page->flash->success(__('c_a_config_navigation_item_enabled'));

				return $this->redirect($this->generateUrl('config_navigation').'?do=items&menu_id='.$iMenuId);
			}
			catch (Exception $e) {
				$this->okt->error->set($e->getMessage());
			}
		}

		# désactivation d'un élément
		if (!empty($_GET['disable']))
		{
			try
			{
				$this->okt->navigation->setItemStatus($_GET['disable'], 0);

				$this->page->flash->success(__('c_a_config_navigation_item_disabled'));

				return $this->redirect($this->generateUrl('config_navigation').'?do=items&menu_id='.$iMenuId);
			}
			catch (Exception $e) {
				$this->okt->error->set($e->getMessage());
			}
		}

		# suppression d'un élément
		if (!empty($_GET['delete']))
		{
			try
			{
				$this->okt->navigation->delItem($_GET['delete']);

				$this->page->flash->success(__('c_a_config_navigation_item_deleted'));

				return $this->redirect($this->generateUrl('config_navigation').'?do=items&menu_id='.$iMenuId);
			}
			catch (Exception $e) {
				$this->okt->error->set($e->getMessage());
			}
		}

		return $this->render('Config/Navigation/Items', array(

		));
	}

	protected function item()
	{
		$iMenuId = !empty($_REQUEST['menu_id']) ? intval($_REQUEST['menu_id']) : null;

		$rsMenu = $this->okt->navigation->getMenu($iMenuId);

		if (empty($iMenuId) || $rsMenu->isEmpty()) {
			return $this->redirect($this->generateUrl('config_navigation'));
		}

		# Données de l'élément
		$aItemData = new ArrayObject();

		$aItemData['item'] = array();
		$aItemData['item']['id'] = null;

		$aItemData['item']['menu_id'] = $iMenuId;
		$aItemData['item']['active'] = 1;
		$aItemData['item']['type'] = 0;

		$aItemData['locales'] = array();

		foreach ($this->okt->languages->list as $aLanguage)
		{
			$aItemData['locales'][$aLanguage['code']] = array();

			$aItemData['locales'][$aLanguage['code']]['title'] = '';
			$aItemData['locales'][$aLanguage['code']]['url'] = '';
		}

		# item update ?
		if (!empty($_REQUEST['item_id']))
		{
			$aItemData['item']['id'] = intval($_REQUEST['item_id']);

			$rsItem = $this->okt->navigation->getItem($aItemData['item']['id']);

			if ($rsItem->isEmpty())
			{
				$this->okt->error->set(sprintf(__('c_a_config_navigation_item_%s_not_exists'), $aItemData['item']['id']));
				$aItemData['item']['id'] = null;
			}
			else
			{
				$aItemData['item']['menu_id'] = $rsItem->menu_id;
				$aItemData['item']['active'] = $rsItem->active;
				$aItemData['item']['type'] = $rsItem->type;

				$rsItemI18n = $this->okt->navigation->getItemI18n($aItemData['item']['id']);

				foreach ($this->okt->languages->list as $aLanguage)
				{
					while ($rsItemI18n->fetch())
					{
						if ($rsItemI18n->language == $aLanguage['code'])
						{
							$aItemData['locales'][$aLanguage['code']]['title'] = $rsItemI18n->title;
							$aItemData['locales'][$aLanguage['code']]['url'] = $rsItemI18n->url;
						}
					}
				}
			}
		}

		#  ajout / modifications d'un élément
		if (!empty($_POST['sended']))
		{
			$aItemData['item']['active'] = !empty($_POST['p_active']) ? 1 : 0;
			$aItemData['item']['type'] = !empty($_POST['p_type']) ? intval($_POST['p_type']) : 0;

			foreach ($this->okt->languages->list as $aLanguage)
			{
				$aItemData['locales'][$aLanguage['code']]['title'] = !empty($_POST['p_title'][$aLanguage['code']]) ? $_POST['p_title'][$aLanguage['code']] : '';
				$aItemData['locales'][$aLanguage['code']]['url'] = !empty($_POST['p_url'][$aLanguage['code']]) ? $_POST['p_url'][$aLanguage['code']] : '';
			}

			# update item
			if (!empty($aItemData['item']['id']))
			{
				if ($this->okt->navigation->checkPostItemData($aItemData) !== false)
				{
					try
					{
						$this->okt->navigation->updItem($aItemData);

						# log admin
						$this->okt->logAdmin->info(array(
							'code' => 41,
							'component' => 'menu item',
							'message' => 'item #'.$aItemData['item']['id']
						));

						$this->page->flash->success(__('c_a_config_navigation_item_updated'));

						return $this->redirect($this->generateUrl('config_navigation').'?do=item&menu_id='.$iMenuId.'&item_id='.$aItemData['item']['id']);
					}
					catch (Exception $e) {
						$this->okt->error->set($e->getMessage());
					}
				}
			}
			# add item
			else
			{
				if ($this->okt->navigation->checkPostItemData($aItemData) !== false)
				{
					try
					{
						$iItemId = $this->okt->navigation->addItem($aItemData);

						# log admin
						$this->okt->logAdmin->info(array(
						'code' => 40,
						'component' => 'menu item',
						'message' => 'item #'.$iItemId
						));

						$this->page->flash->success(__('c_a_config_navigation_item_added'));

						return $this->redirect($this->generateUrl('config_navigation').'?do=item&menu_id='.$iMenuId.'&item_id='.$iItemId);
					}
					catch (Exception $e) {
						$this->okt->error->set($e->getMessage());
					}
				}
			}
		}

		return $this->render('Config/Navigation/Item', array(

		));
	}

	protected function config()
	{
		$oTemplates = new TemplatesSet($this->okt,
			$this->okt->config->navigation_tpl,
			'navigation',
			'navigation',
			$this->generateUrl('config_navigation').'?do=config&amp;'
		);

		if ($this->request->request->has('sended'))
		{
			$p_tpl = $oTemplates->getPostConfig();

			if ($this->okt->error->isEmpty())
			{
				$new_conf = array(
					'navigation_tpl' => $p_tpl
				);

				try
				{
					$this->okt->config->write($new_conf);

					$this->page->flash->success(__('c_c_confirm_configuration_updated'));

					return $this->redirect($this->generateUrl('config_navigation').'?do=config');

				}
				catch (InvalidArgumentException $e)
				{
					$this->okt->error->set(__('c_c_error_writing_configuration'));
					$this->okt->error->set($e->getMessage());
				}
			}
		}

		return $this->render('Config/Navigation/Config', array(
			'oTemplates' => $oTemplates
		));
	}

}