<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Module\Pages\Admin\Controller;

use Tao\Admin\Controller;
use Tao\Themes\TemplatesSet;

class Post extends Controller
{
	protected $aPageData;

	public function add()
	{
		if (!$this->okt->checkPerm('pages_add')) {
			return $this->serve401();
		}

		$this->init();

		if ($this->populateDataFromPost())
		{
			try
			{
				# -- TRIGGER MODULE PAGES : beforePageCreate
				$this->okt->Pages->triggers->callTrigger('beforePageCreate', $this->okt, $this->aPageData);

				$this->aPageData['post']['id'] = $this->okt->Pages->addPage($this->aPageData['cursor'], $this->aPageData['locales'], $this->aPageData['perms']);

				# -- TRIGGER MODULE PAGES : afterPageCreate
				$this->okt->Pages->triggers->callTrigger('afterPageCreate', $this->okt, $this->aPageData);

				# log admin
				$this->okt->logAdmin->info(array(
					'code' => 40,
					'component' => 'pages',
					'message' => 'page #'.$this->aPageData['post']['id']
				));

				$this->okt->page->flash->success(__('m_pages_page_added'));

				return $this->redirect($this->generateUrl('Pages_post', array('page_id' => $this->aPageData['post']['id'])));
			}
			catch (Exception $e) {
				$this->okt->error->set($e->getMessage());
			}
		}

		$this->display();
	}

	public function edit()
	{
		$this->init();

		$this->aPageData['post']['id'] = $this->request->attributes->getInt('page_id');

		$rsPage = $this->okt->Pages->getPagesRecordset(array(
			'id' => $this->aPageData['post']['id'],
			'active' => 2
		));

		if (null === $this->aPageData['post']['id'] || $rsPage->isEmpty())
		{
			$this->page->flash->error(sprintf(__('m_pages_page_%s_not_exists'), $this->aPageData['post']['id']));

			return $this->serve404();
		}

		$this->aPageData['post']['category_id'] = $rsPage->category_id;
		$this->aPageData['post']['active'] = $rsPage->active;
		$this->aPageData['post']['tpl'] = $rsPage->tpl;
		$this->aPageData['post']['created_at'] = $rsPage->created_at;
		$this->aPageData['post']['updated_at'] = $rsPage->updated_at;

		$rsPageI18n = $this->okt->Pages->getPageI18n($this->aPageData['post']['id']);

		foreach ($this->okt->languages->list as $aLanguage)
		{
			while ($rsPageI18n->fetch())
			{
				if ($rsPageI18n->language == $aLanguage['code'])
				{
					$this->aPageData['locales'][$aLanguage['code']]['title'] = $rsPageI18n->title;
					$this->aPageData['locales'][$aLanguage['code']]['subtitle'] = $rsPageI18n->subtitle;
					$this->aPageData['locales'][$aLanguage['code']]['content'] = $rsPageI18n->content;

					if ($this->okt->Pages->config->enable_metas)
					{
						$this->aPageData['locales'][$aLanguage['code']]['title_seo'] = $rsPageI18n->title_seo;
						$this->aPageData['locales'][$aLanguage['code']]['title_tag'] = $rsPageI18n->title_tag;
						$this->aPageData['locales'][$aLanguage['code']]['slug'] = $rsPageI18n->slug;
						$this->aPageData['locales'][$aLanguage['code']]['meta_description'] = $rsPageI18n->meta_description;
						$this->aPageData['locales'][$aLanguage['code']]['meta_keywords'] = $rsPageI18n->meta_keywords;
					}
				}
			}
		}

		# Images
		if ($this->okt->Pages->config->images['enable']) {
			$this->aPageData['images'] = $rsPage->getImagesInfo();
		}

		# Fichiers
		if ($this->okt->Pages->config->files['enable']) {
			$this->aPageData['files'] = $rsPage->getFilesInfo();
		}

		# Permissions
		if ($this->okt->Pages->canUsePerms()) {
			$this->aPageData['perms'] = $this->okt->Pages->getPagePermissions($this->aPageData['post']['id']);
		}

		# switch page status
		if ($this->request->query->has('switch_status'))
		{
			try
			{
				$this->okt->Pages->switchPageStatus($this->aPageData['post']['id']);

				# log admin
				$this->okt->logAdmin->info(array(
					'code' => 32,
					'component' => 'pages',
					'message' => 'page #'.$this->aPageData['post']['id']
				));

				return $this->redirect($this->generateUrl('Pages_post', array('page_id' => $this->aPageData['post']['id'])));
			}
			catch (Exception $e) {
				$this->okt->error->set($e->getMessage());
			}
		}

		# suppression d'une image
		if ($this->request->query->has('delete_image'))
		{
			$this->okt->Pages->deleteImage($this->aPageData['post']['id'], $this->request->query->get('delete_image'));

			# log admin
			$this->okt->logAdmin->info(array(
				'code' => 41,
				'component' => 'pages',
				'message' => 'page #'.$this->aPageData['post']['id']
			));

			$this->okt->page->flash->success(__('m_pages_page_updated'));

			return $this->redirect($this->generateUrl('Pages_post', array('page_id' => $this->aPageData['post']['id'])));
		}

		# suppression d'un fichier
		if ($this->request->query->has('delete_file'))
		{
			$this->okt->Pages->deleteFile($this->aPageData['post']['id'], $this->request->query->get('delete_file'));

			# log admin
			$this->okt->logAdmin->info(array(
				'code' => 41,
				'component' => 'pages',
				'message' => 'page #'.$this->aPageData['post']['id']
			));

			$this->okt->page->flash->success(__('m_pages_page_updated'));

			return $this->redirect($this->generateUrl('Pages_post', array('page_id' => $this->aPageData['post']['id'])));
		}

		if ($this->populateDataFromPost())
		{
			try
			{
				# -- TRIGGER MODULE PAGES : beforePageUpdate
				$this->okt->Pages->triggers->callTrigger('beforePageUpdate', $this->okt, $this->aPageData);

				$this->okt->Pages->updPage($this->aPageData['cursor'], $this->aPageData['locales'], $this->aPageData['perms']);

				# -- TRIGGER MODULE PAGES : afterPageUpdate
				$this->okt->Pages->triggers->callTrigger('afterPageUpdate', $this->okt, $this->aPageData);

				# log admin
				$this->okt->logAdmin->info(array(
					'code' => 41,
					'component' => 'pages',
					'message' => 'page #'.$this->aPageData['post']['id']
				));

				$this->okt->page->flash->success(__('m_pages_page_updated'));

				return $this->redirect($this->generateUrl('Pages_post', array('page_id' => $this->aPageData['post']['id'])));
			}
			catch (Exception $e) {
				$this->okt->error->set($e->getMessage());
			}
		}

		$this->display();
	}

	protected function init()
	{
		# Chargement des locales
		$this->okt->l10n->loadFile(__DIR__.'/../../locales/'.$this->okt->user->language.'/admin.post');

		# Données de la page
		$this->aPageData = new \ArrayObject();

		$this->aPageData['post'] = array();
		$this->aPageData['post']['id'] = null;

		$this->aPageData['post']['category_id'] = 0;
		$this->aPageData['post']['active'] = 1;
		$this->aPageData['post']['tpl'] = '';
		$this->aPageData['post']['created_at'] = '';
		$this->aPageData['post']['updated_at'] = '';

		$this->aPageData['locales'] = array();

		foreach ($this->okt->languages->list as $aLanguage)
		{
			$this->aPageData['locales'][$aLanguage['code']] = array();

			$this->aPageData['locales'][$aLanguage['code']]['title'] = '';
			$this->aPageData['locales'][$aLanguage['code']]['subtitle'] = '';
			$this->aPageData['locales'][$aLanguage['code']]['content'] = '';

			if ($this->okt->Pages->config->enable_metas)
			{
				$this->aPageData['locales'][$aLanguage['code']]['title_seo'] = '';
				$this->aPageData['locales'][$aLanguage['code']]['title_tag'] = '';
				$this->aPageData['locales'][$aLanguage['code']]['slug'] = '';
				$this->aPageData['locales'][$aLanguage['code']]['meta_description'] = '';
				$this->aPageData['locales'][$aLanguage['code']]['meta_keywords'] = '';
			}
		}

		$this->aPageData['perms'] = array(0);
		$this->aPageData['images'] = array();
		$this->aPageData['files'] = array();

		$rsPage = null;
		$rsPageI18n = null;

		# -- TRIGGER MODULE PAGES : adminPostInit
		$this->okt->Pages->triggers->callTrigger('adminPostInit', $this->okt, $this->aPageData, $rsPage, $rsPageI18n);
	}

	protected function populateDataFromPost()
	{
		if (!$this->request->request->has('sended')) {
			return false;
		}

		$this->aPageData['post']['category_id'] = !empty($_POST['p_category_id']) ? intval($_POST['p_category_id']) : 0;
		$this->aPageData['post']['active'] = !empty($_POST['p_active']) ? 1 : 0;
		$this->aPageData['post']['tpl'] = !empty($_POST['p_tpl']) ? $_POST['p_tpl'] : null;
		$this->aPageData['post']['created_at'] = $this->aPageData['post']['created_at'];
		$this->aPageData['post']['updated_at'] = $this->aPageData['post']['updated_at'];

		foreach ($this->okt->languages->list as $aLanguage)
		{
			$this->aPageData['locales'][$aLanguage['code']]['title'] = !empty($_POST['p_title'][$aLanguage['code']]) ? $_POST['p_title'][$aLanguage['code']] : '';
			$this->aPageData['locales'][$aLanguage['code']]['subtitle'] = !empty($_POST['p_subtitle'][$aLanguage['code']]) ? $_POST['p_subtitle'][$aLanguage['code']] : '';
			$this->aPageData['locales'][$aLanguage['code']]['content'] = !empty($_POST['p_content'][$aLanguage['code']]) ? $_POST['p_content'][$aLanguage['code']] : '';

			if ($this->okt->Pages->config->enable_metas)
			{
				$this->aPageData['locales'][$aLanguage['code']]['title_seo'] = !empty($_POST['p_title_seo'][$aLanguage['code']]) ? $_POST['p_title_seo'][$aLanguage['code']] : '';
				$this->aPageData['locales'][$aLanguage['code']]['title_tag'] = !empty($_POST['p_title_tag'][$aLanguage['code']]) ? $_POST['p_title_tag'][$aLanguage['code']] : '';
				$this->aPageData['locales'][$aLanguage['code']]['slug'] = !empty($_POST['p_slug'][$aLanguage['code']]) ? $_POST['p_slug'][$aLanguage['code']] : '';
				$this->aPageData['locales'][$aLanguage['code']]['meta_description'] = !empty($_POST['p_meta_description'][$aLanguage['code']]) ? $_POST['p_meta_description'][$aLanguage['code']] : '';
				$this->aPageData['locales'][$aLanguage['code']]['meta_keywords'] = !empty($_POST['p_meta_keywords'][$aLanguage['code']]) ? $_POST['p_meta_keywords'][$aLanguage['code']] : '';
			}
		}

		$this->aPageData['perms'] = !empty($_POST['perms']) ? $_POST['perms'] : array();

		# -- TRIGGER MODULE PAGES : adminPopulateData
		$this->okt->Pages->triggers->callTrigger('adminPopulateData', $this->okt, $this->aPageData);

		# vérification des données avant modification dans la BDD
		if ($this->okt->Pages->checkPostData($this->aPageData))
		{
			$this->aPageData['cursor'] = $this->okt->Pages->openPageCursor($this->aPageData['post']);

			return true;
		}

		return false;
	}

	protected function display()
	{
		# Récupération de la liste complète des rubriques
		$rsCategories = null;
		if ($this->okt->Pages->config->categories['enable'])
		{
			$rsCategories = $this->okt->Pages->categories->getCategories(array(
				'active' => 2,
				'language' => $this->okt->user->language
			));
		}

		# Liste des templates utilisables
		$oTemplatesItem = new TemplatesSet($this->okt, $this->okt->Pages->config->templates['item'], 'pages/item', 'item');
		$aTplChoices = array_merge(
			array('&nbsp;' => null),
			$oTemplatesItem->getUsablesTemplatesForSelect($this->okt->Pages->config->templates['item']['usables'])
		);

		# Récupération de la liste des groupes si les permissions sont activées
		$aGroups = null;
		if ($this->okt->Pages->canUsePerms()) {
			$aGroups = $this->okt->Pages->getUsersGroupsForPerms(false,true);
		}

		# Construction des onglets
		$this->aPageData['tabs'] = new \ArrayObject;

		# onglet contenu
		$this->aPageData['tabs'][10] = array(
			'id' => 'tab-content',
			'title' => __('m_pages_page_tab_content'),
			'content' => $this->renderView('Pages/Admin/Templates/Post/Tabs/Content', array(
				'aPageData' => $this->aPageData
			))
		);

		# onglet images
		if ($this->okt->Pages->config->images['enable'])
		{
			$this->aPageData['tabs'][20] = array(
				'id' => 'tab-images',
				'title' => __('m_pages_page_tab_images'),
				'content' => $this->renderView('Pages/Admin/Templates/Post/Tabs/Images', array(
					'aPageData' 	=> $this->aPageData
				))
			);
		}

		# onglet fichiers
		if ($this->okt->Pages->config->files['enable'])
		{
			$this->aPageData['tabs'][30] = array(
				'id' => 'tab-files',
				'title' => __('m_pages_page_tab_files'),
				'content' => $this->renderView('Pages/Admin/Templates/Post/Tabs/Files', array(
					'aPageData' 	=> $this->aPageData
				))
			);
		}

		# onglet options
		$this->aPageData['tabs'][40] = array(
			'id' => 'tab-options',
			'title' => __('m_pages_page_tab_options'),
			'content' => $this->renderView('Pages/Admin/Templates/Post/Tabs/Options', array(
				'rsCategories' 	=> $rsCategories,
				'aPageData' 	=> $this->aPageData
			))
		);

		# onglet seo
		if ($this->okt->Pages->config->enable_metas)
		{
			$this->aPageData['tabs'][50] = array(
				'id' => 'tab-seo',
				'title' => __('m_pages_page_tab_seo'),
				'content' => $this->renderView('Pages/Admin/Templates/Post/Tabs/Seo', array(
					'aPageData' 	=> $this->aPageData
				))
			);
		}

		# -- TRIGGER MODULE PAGES : adminPostBuildTabs
		$this->okt->Pages->triggers->callTrigger('adminPostBuildTabs', $this->okt, $this->aPageData);

		$this->aPageData['tabs']->ksort();

		return $this->render('Pages/Admin/Templates/Post/Page', array(
			'aPageData' 	=> $this->aPageData,
			'rsCategories' 	=> $rsCategories,
			'aTplChoices' 	=> $aTplChoices,
			'aGroups' 		=> $aGroups
		));
	}
}
