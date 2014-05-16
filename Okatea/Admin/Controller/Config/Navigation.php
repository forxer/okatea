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
		if (! $this->okt->checkPerm('is_superadmin'))
		{
			return $this->serve401();
		}
		
		# locales
		$this->okt->l10n->loadFile($this->okt->options->locales_dir . '/%s/admin/navigation');
		
		# titre et fil d'ariane
		$this->page->addGlobalTitle(__('c_a_config_navigation'), $this->generateUrl('config_navigation'));
		
		$sDo = $this->request->query->get('do');
		if (! $sDo || $sDo === 'index')
		{
			return $this->index();
		}
		elseif ($sDo === 'menu')
		{
			return $this->menu();
		}
		elseif ($sDo === 'items')
		{
			return $this->items();
		}
		elseif ($sDo === 'item')
		{
			return $this->item();
		}
		elseif ($sDo === 'config')
		{
			return $this->config();
		}
		else
		{
			return $this->serve404();
		}
	}

	protected function index()
	{
		# switch statut
		$iMenuIdSwitchStatus = $this->request->query->getInt('switch_status');
		
		if ($iMenuIdSwitchStatus)
		{
			try
			{
				$this->okt->navigation->switchMenuStatus($iMenuIdSwitchStatus);
				
				$this->page->flash->success(__('c_a_config_navigation_menu_switched'));
				
				return $this->redirect($this->generateUrl('config_navigation'));
			}
			catch (Exception $e)
			{
				$this->okt->error->set($e->getMessage());
			}
		}
		
		# suppression d'un menu
		$iMenuIdDelete = $this->request->query->getInt('delete_menu');
		if ($iMenuIdDelete)
		{
			try
			{
				$this->okt->navigation->delMenu($iMenuIdDelete);
				
				$this->page->flash->success(__('c_a_config_navigation_menu_deleted'));
				
				return $this->redirect($this->generateUrl('config_navigation') . '?do=index');
			}
			catch (Exception $e)
			{
				$this->okt->error->set($e->getMessage());
			}
		}
		
		return $this->render('Config/Navigation/Index', array()

		);
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
		$iMenuId = $this->request->query->getInt('menu_id', $this->request->request->getInt('menu_id'));
		if ($iMenuId)
		{
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
		if ($this->request->request->has('sended'))
		{
			$aMenuData = array(
				'title' => $this->request->request->get('p_title', ''),
				'active' => $this->request->request->has('p_active') ? 1 : 0,
				'tpl' => $this->request->request->get('p_tpl', '')
			);
			
			# update menu
			if (! empty($iMenuId))
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
							'message' => 'menu #' . $iMenuId
						));
						
						$this->page->flash->success(__('c_a_config_navigation_menu_updated'));
						
						return $this->redirect($this->generateUrl('config_navigation') . '?do=menu&menu_id=' . $iMenuId);
					}
					catch (Exception $e)
					{
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
							'message' => 'menu #' . $iMenuId
						));
						
						$this->page->flash->success(__('c_a_config_navigation_menu_added'));
						
						return $this->redirect($this->generateUrl('config_navigation') . '?do=menu&menu_id=' . $iMenuId);
					}
					catch (Exception $e)
					{
						$this->okt->error->set($e->getMessage());
					}
				}
			}
		}
		
		# Liste des templates utilisables
		$oTemplates = new TemplatesSet($this->okt, $this->okt->config->navigation_tpl, 'navigation', 'navigation');
		$aTplChoices = array_merge(array(
			'&nbsp;' => null
		), $oTemplates->getUsablesTemplatesForSelect($this->okt->config->navigation_tpl['usables']));
		
		return $this->render('Config/Navigation/Menu', array(
			'iMenuId' => $iMenuId,
			'aMenuData' => $aMenuData,
			'aTplChoices' => $aTplChoices
		));
	}

	protected function items()
	{
		$iMenuId = $this->request->query->getInt('menu_id', $this->request->request->getInt('menu_id'));
		
		$rsMenu = $this->okt->navigation->getMenu($iMenuId);
		
		if (empty($iMenuId) || $rsMenu->isEmpty())
		{
			return $this->redirect($this->generateUrl('config_navigation'));
		}
		
		# AJAX : changement de l'ordre des éléments
		if ($this->request->query->has('ajax_update_order'))
		{
			$aItemsOrder = $this->request->query->get('ord', array());
			
			if (! empty($aItemsOrder))
			{
				foreach ($aItemsOrder as $ord => $id)
				{
					$ord = ((integer) $ord) + 1;
					$this->okt->navigation->updItemOrder($id, $ord);
				}
			}
			
			exit();
		}
		
		# POST : changement de l'ordre des langues
		if ($this->request->request->has('order_items'))
		{
			try
			{
				$aItemsOrder = $this->request->query->get('p_order', array());
				
				asort($aItemsOrder);
				
				$aItemsOrder = array_keys($aItemsOrder);
				
				if (! empty($aItemsOrder))
				{
					foreach ($aItemsOrder as $ord => $id)
					{
						$ord = ((integer) $ord) + 1;
						$this->okt->navigation->updItemOrder($id, $ord);
					}
					
					$this->page->flash->success(__('c_a_config_navigation_items_neworder'));
					
					return $this->redirect($this->generateUrl('config_navigation') . '?do=items&menu_id=' . $iMenuId);
				}
			}
			catch (Exception $e)
			{
				$this->okt->error->set($e->getMessage());
			}
		}
		
		# activation d'un élément
		$iItemIdEnable = $this->request->query->getInt('enable');
		if ($iItemIdEnable)
		{
			try
			{
				$this->okt->navigation->setItemStatus($iItemIdEnable, 1);
				
				$this->page->flash->success(__('c_a_config_navigation_item_enabled'));
				
				return $this->redirect($this->generateUrl('config_navigation') . '?do=items&menu_id=' . $iMenuId);
			}
			catch (Exception $e)
			{
				$this->okt->error->set($e->getMessage());
			}
		}
		
		# désactivation d'un élément
		$iItemIdDisable = $this->request->query->getInt('disable');
		if ($iItemIdDisable)
		{
			try
			{
				$this->okt->navigation->setItemStatus($iItemIdDisable, 0);
				
				$this->page->flash->success(__('c_a_config_navigation_item_disabled'));
				
				return $this->redirect($this->generateUrl('config_navigation') . '?do=items&menu_id=' . $iMenuId);
			}
			catch (Exception $e)
			{
				$this->okt->error->set($e->getMessage());
			}
		}
		
		# suppression d'un élément
		$iItemIdDelete = $this->request->query->getInt('delete');
		if ($iItemIdDelete)
		{
			try
			{
				$this->okt->navigation->delItem($iItemIdDelete);
				
				$this->page->flash->success(__('c_a_config_navigation_item_deleted'));
				
				return $this->redirect($this->generateUrl('config_navigation') . '?do=items&menu_id=' . $iMenuId);
			}
			catch (Exception $e)
			{
				$this->okt->error->set($e->getMessage());
			}
		}
		
		$rsItems = $this->okt->navigation->getItems(array(
			'menu_id' => $iMenuId,
			'language' => $this->okt->user->language,
			'active' => 2
		));
		
		return $this->render('Config/Navigation/Items', array(
			'iMenuId' => $iMenuId,
			'rsMenu' => $rsMenu,
			'rsItems' => $rsItems
		));
	}

	protected function item()
	{
		$iMenuId = $this->request->query->getInt('menu_id', $this->request->request->getInt('menu_id'));
		
		$rsMenu = $this->okt->navigation->getMenu($iMenuId);
		
		if (empty($iMenuId) || $rsMenu->isEmpty())
		{
			return $this->redirect($this->generateUrl('config_navigation'));
		}
		
		# Données de l'élément
		$aItemData = new ArrayObject();
		
		$aItemData['item'] = array();
		$aItemData['item']['id'] = $this->request->query->getInt('item_id', $this->request->request->getInt('item_id'));
		
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
		if ($aItemData['item']['id'])
		{
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
				
				$rsItemI18n = $this->okt->navigation->getItemL10n($aItemData['item']['id']);
				
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
		if ($this->request->request->has('sended'))
		{
			$aItemData['item']['active'] = $this->request->request->has('p_active') ? 1 : 0;
			$aItemData['item']['type'] = $this->request->request->has('p_type') ? 1 : 0;
			
			foreach ($this->okt->languages->list as $aLanguage)
			{
				$aItemData['locales'][$aLanguage['code']]['title'] = $this->request->request->get('p_title[' . $aLanguage['code'] . ']', '', true);
				$aItemData['locales'][$aLanguage['code']]['url'] = $this->request->request->get('p_url[' . $aLanguage['code'] . ']', '', true);
			}
			
			# update item
			if (! empty($aItemData['item']['id']))
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
							'message' => 'item #' . $aItemData['item']['id']
						));
						
						$this->page->flash->success(__('c_a_config_navigation_item_updated'));
						
						return $this->redirect($this->generateUrl('config_navigation') . '?do=item&menu_id=' . $iMenuId . '&item_id=' . $aItemData['item']['id']);
					}
					catch (Exception $e)
					{
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
							'message' => 'item #' . $iItemId
						));
						
						$this->page->flash->success(__('c_a_config_navigation_item_added'));
						
						return $this->redirect($this->generateUrl('config_navigation') . '?do=item&menu_id=' . $iMenuId . '&item_id=' . $iItemId);
					}
					catch (Exception $e)
					{
						$this->okt->error->set($e->getMessage());
					}
				}
			}
		}
		
		return $this->render('Config/Navigation/Item', array(
			'iMenuId' => $iMenuId,
			'rsMenu' => $rsMenu,
			'aItemData' => $aItemData
		));
	}

	protected function config()
	{
		$oTemplates = new TemplatesSet($this->okt, $this->okt->config->navigation_tpl, 'navigation', 'navigation', $this->generateUrl('config_navigation') . '?do=config&amp;');
		
		if ($this->request->request->has('sended'))
		{
			$p_tpl = $oTemplates->getPostConfig();
			
			if ($this->okt->error->isEmpty())
			{
				$this->okt->config->write(array(
					'navigation_tpl' => $p_tpl
				));
				
				$this->page->flash->success(__('c_c_confirm_configuration_updated'));
				
				return $this->redirect($this->generateUrl('config_navigation') . '?do=config');
			}
		}
		
		return $this->render('Config/Navigation/Config', array(
			'oTemplates' => $oTemplates
		));
	}
}
