<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Pages;

use Okatea\Admin\Menu as AdminMenu;
use Okatea\Admin\Page;
use Okatea\Tao\Html\Modifiers;
use Okatea\Tao\Extensions\Modules\Module as BaseModule;
use Okatea\Tao\Triggers\Triggers;

class Module extends BaseModule
{
	public $config;

	public $categories;

	public $filters;

	protected $t_pages;

	protected $t_pages_locales;

	protected $t_categories;

	protected $t_categories_locales;

	protected $t_permissions;

	protected $aParams = array();

	protected function prepend()
	{
		# permissions
		$this->okt->addPermGroup('pages', __('m_pages_perm_group'));
		$this->okt->addPerm('pages', __('m_pages_perm_global'), 'pages');
		$this->okt->addPerm('pages_categories', __('m_pages_perm_categories'), 'pages');
		$this->okt->addPerm('pages_add', __('m_pages_perm_add'), 'pages');
		$this->okt->addPerm('pages_remove', __('m_pages_perm_remove'), 'pages');
		$this->okt->addPerm('pages_display', __('m_pages_perm_display'), 'pages');
		$this->okt->addPerm('pages_config', __('m_pages_perm_config'), 'pages');

		# tables
		$this->t_pages = $this->db->prefix . 'mod_pages';
		$this->t_pages_locales = $this->db->prefix . 'mod_pages_locales';
		$this->t_permissions = $this->db->prefix . 'mod_pages_permissions';
		$this->t_categories = $this->db->prefix . 'mod_pages_categories';
		$this->t_categories_locales = $this->db->prefix . 'mod_pages_categories_locales';

		# déclencheurs
		$this->triggers = new Triggers();

		# config
		$this->config = $this->okt->newConfig('conf_pages');

		# pages manager
		$this->pages = new Pages($this->okt, $this->t_pages, $this->t_pages_locales, $this->t_permissions, $this->t_categories, $this->t_categories_locales);

		# rubriques
		if ($this->config->categories['enable'])
		{
			$this->categories = new Categories($this->okt, $this->t_pages, $this->t_pages_locales, $this->t_categories, $this->t_categories_locales, 'id', 'parent_id', 'ord', 'category_id', 'language', array(
				'active',
				'ord'
			), array(
				'title',
				'title_tag',
				'title_seo',
				'slug',
				'content',
				'meta_description',
				'meta_keywords'
			));
		}
	}

	protected function prepend_admin()
	{
		# on ajoutent un item au menu admin
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->pagesSubMenu = new AdminMenu(null, Page::$formatHtmlSubMenu);
			$this->okt->page->mainMenu->add($this->getName(), $this->okt->adminRouter->generate('Pages_index'), $this->okt['request']->attributes->get('_route') === 'Pages_index', 20, $this->okt->checkPerm('pages'), null, $this->okt->page->pagesSubMenu, $this->okt['public_url'] . '/modules/Pages/module_icon.png');
			$this->okt->page->pagesSubMenu->add(__('c_a_menu_management'), $this->okt->adminRouter->generate('Pages_index'), in_array($this->okt['request']->attributes->get('_route'), array(
				'Pages_index',
				'Pages_post'
			)), 1);
			$this->okt->page->pagesSubMenu->add(__('m_pages_menu_add_page'), $this->okt->adminRouter->generate('Pages_post_add'), $this->okt['request']->attributes->get('_route') === 'Pages_post_add', 2, $this->okt->checkPerm('pages_add'));
			$this->okt->page->pagesSubMenu->add(__('m_pages_menu_categories'), $this->okt->adminRouter->generate('Pages_categories'), in_array($this->okt['request']->attributes->get('_route'), array(
				'Pages_categories',
				'Pages_category',
				'Pages_category_add'
			)), 3, ($this->config->categories['enable'] && $this->okt->checkPerm('pages_categories')));
			$this->okt->page->pagesSubMenu->add(__('c_a_menu_display'), $this->okt->adminRouter->generate('Pages_display'), $this->okt['request']->attributes->get('_route') === 'Pages_display', 10, $this->okt->checkPerm('pages_display'));
			$this->okt->page->pagesSubMenu->add(__('c_a_menu_configuration'), $this->okt->adminRouter->generate('Pages_config'), $this->okt['request']->attributes->get('_route') === 'Pages_config', 20, $this->okt->checkPerm('pages_config'));
		}

		$this->okt['triggers']->registerTrigger('adminConfigSiteInit', array(
			$this,
			'adminConfigSiteInit'
		));
	}

	protected function prepend_public()
	{
		$this->okt['triggers']->registerTrigger('handleWebsiteHomePage', array(
			$this,
			'handleWebsiteHomePage'
		));

		$this->okt['triggers']->registerTrigger('websiteAdminBarItems', array(
			$this,
			'websiteAdminBarItems'
		));
	}

	public function handleWebsiteHomePage($item, $details)
	{
		if ($item == 'pagesItem')
		{
			$this->okt->controllerInstance = new Controller($this->okt);
			$this->okt->response = $this->okt->controllerInstance->pagesItemForHomePage($details);
		}
	}

	public function adminConfigSiteInit($aPageData)
	{
		$aPageData['home_page_items'][__('m_pages_config_homepage_item')] = 'pagesItem';

		$rsPages = $this->pages->getPagesRecordset();
		$aPages = array();
		while ($rsPages->fetch())
		{
			$aPages[$rsPages->language][] = array(
				'id' => $rsPages->id,
				'title' => $rsPages->title
			);
		}

		foreach ($this->okt['config']->home_page['item'] as $language => $item)
		{
			if ($item == 'pagesItem' && ! empty($aPages[$language]))
			{
				foreach ($aPages[$language] as $page)
				{
					$aPageData['home_page_details'][$language][$page['title']] = $page['id'];
				}
			}
		}

		$this->okt->page->js->addScript('
			var pages = ' . json_encode($aPages) . ';
		');

		foreach ($this->okt['languages']->list as $aLanguage)
		{
			$this->okt->page->js->addReady('
				$("#p_home_page_item_' . $aLanguage['code'] . '").change(function(){

					var selected = $("#p_home_page_item_' . $aLanguage['code'] . ' option:selected").val();
					var details = $("#p_home_page_details_' . $aLanguage['code'] . '");

					if (selected == "pagesList") {
						details.find("option").remove();
					}
					else if (selected == "pagesItem")
					{
						$(pages.' . $aLanguage['code'] . ').each(function() {
							details.append($("<option>").attr("value", this.id).text(this.title));
						});
					}
				});
			');
		}
	}

	/**
	 * Ajout d'éléments à la barre admin côté publique.
	 *
	 * @param arrayObject $aPrimaryAdminBar
	 * @param arrayObject $aSecondaryAdminBar
	 * @param arrayObject $aBasesUrl
	 * @return void
	 */
	public function websiteAdminBarItems($aPrimaryAdminBar, $aSecondaryAdminBar, $aBasesUrl)
	{
		# lien ajouter une page
		if ($this->okt->checkPerm('pages_add'))
		{
			$aPrimaryAdminBar[200]['items'][100] = array(
				'href' => $aBasesUrl['admin'] . '/module.php?m=pages&amp;action=add',
				'title' => __('m_pages_ab_page_title'),
				'intitle' => __('m_pages_ab_page')
			);
		}

		# modification de la page en cours
		if (isset($this->okt->page->module) && $this->okt->page->module == 'pages' && isset($this->okt->page->action) && $this->okt->page->action == 'item')
		{
			if (isset($this->okt->controller->rsPage) && $this->okt->controller->rsPage->isEditable())
			{
				$aPrimaryAdminBar[300] = array(
					'href' => $aBasesUrl['admin'] . '/module.php?m=pages&amp;action=edit&amp;post_id=' . $this->okt->controller->rsPage->id,
					'intitle' => __('m_pages_ab_edit_page')
				);
			}
		}
	}

	/**
	 * Indique si on as accès à la partie publique en fonction de la configuration.
	 *
	 * @return boolean
	 */
	public function isPublicAccessible()
	{
		# si on est superadmin on as droit à tout
		if ($this->okt->user->is_superadmin)
		{
			return true;
		}

		# si on a le groupe id 0 (zero) alors tous le monde a droit
		# sinon il faut etre dans le bon groupe
		if (in_array(0, $this->config->perms) || in_array($this->okt->user->group_id, $this->config->perms))
		{
			return true;
		}

		# toutes éventualités testées, on as pas le droit
		return false;
	}

	/**
	 * Initialisation des filtres
	 *
	 * @param string $part
	 *        	'public' ou 'admin'
	 */
	public function filtersStart($part = 'public')
	{
		if ($this->filters === null || ! ($this->filters instanceof Filters))
		{
			$this->filters = new Filters($this->okt, $part);
		}
	}

	/**
	 * Retourne le chemin du template de la liste des pages.
	 *
	 * @return string
	 */
	public function getListTplPath()
	{
		return 'Pages/list/' . $this->config->templates['list']['default'] . '/template';
	}

	/**
	 * Retourne le chemin du template du flux des pages.
	 *
	 * @return string
	 */
	public function getFeedTplPath()
	{
		return 'Pages/feed/' . $this->config->templates['feed']['default'] . '/template';
	}

	/**
	 * Retourne le chemin du template de l'encart des pages.
	 *
	 * @return string
	 */
	public function getInsertTplPath()
	{
		return 'Pages/insert/' . $this->config->templates['insert']['default'] . '/template';
	}

	/**
	 * Retourne le chemin du template de la liste des pages d'une rubrique.
	 *
	 * @return string
	 */
	public function getCategoryTplPath($sCategoryTemplate = null)
	{
		$sTemplate = $this->config->templates['list']['default'];

		if (! empty($sCategoryTemplate) && in_array($sCategoryTemplate, $this->config->templates['list']['usables']))
		{
			$sTemplate = $sCategoryTemplate;
		}

		return 'Pages/list/' . $sTemplate . '/template';
	}

	/**
	 * Retourne le chemin du template d'une page.
	 *
	 * @return string
	 */
	public function getItemTplPath($sPageTemplate = null, $sCatPageTemplate = null)
	{
		$sTemplate = $this->config->templates['item']['default'];

		if (! empty($sPageTemplate) && in_array($sPageTemplate, $this->config->templates['item']['usables']))
		{
			$sTemplate = $sPageTemplate;
		}
		elseif (! empty($sCatPageTemplate) && in_array($sCatPageTemplate, $this->config->templates['item']['usables']))
		{
			$sTemplate = $sCatPageTemplate;
		}

		return 'Pages/item/' . $sTemplate . '/template';
	}

	/**
	 * Reconstruction des index de recherche de toutes les pages.
	 */
	public function indexAllPages()
	{
		$rsPages = $this->pages->getPages(array(
			'active' => 2
		));

		while ($rsPages->fetch())
		{
			$words = $rsPages->title . ' ' . $rsPages->subtitle . ' ' . $rsPages->content . ' ';

			$words = implode(' ', Modifiers::splitWords($words));

			$query = 'UPDATE ' . $this->t_pages . ' SET ' . 'words=\'' . $this->db->escapeStr($words) . '\' ' . 'WHERE id=' . (integer) $rsPages->id;

			$this->db->execute($query);
		}

		return true;
	}
}
