<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Pages\Admin\Controller;

use ArrayObject;
use Okatea\Admin\Controller;
use Okatea\Tao\Themes\TemplatesSet;

class Post extends Controller
{

	protected $aPageData;

	public function add()
	{
		if (! $this->okt->checkPerm('pages_add'))
		{
			return $this->serve401();
		}

		$this->init();

		if ($this->populateDataFromPost())
		{
			try
			{
				# -- TRIGGER MODULE PAGES : beforePageCreate
				$this->okt->module('Pages')->triggers->callTrigger('beforePageCreate', $this->aPageData);

				$this->aPageData['post']['id'] = $this->okt->module('Pages')->pages->addPage($this->aPageData['cursor'], $this->aPageData['locales'], $this->aPageData['perms']);

				# -- TRIGGER MODULE PAGES : afterPageCreate
				$this->okt->module('Pages')->triggers->callTrigger('afterPageCreate', $this->aPageData);

				# log admin
				$this->okt->logAdmin->info(array(
					'code' => 40,
					'component' => 'pages',
					'message' => 'page #' . $this->aPageData['post']['id']
				));

				$this->okt->page->flash->success(__('m_pages_page_added'));

				return $this->redirect($this->generateUrl('Pages_post', array(
					'page_id' => $this->aPageData['post']['id']
				)));
			}
			catch (\Exception $e)
			{
				$this->okt->error->set($e->getMessage());
			}
		}

		return $this->display();
	}

	public function edit()
	{
		$this->init();

		$this->aPageData['post']['id'] = $this->request->attributes->getInt('page_id');

		$rsPage = $this->okt->module('Pages')->pages->getPagesRecordset(array(
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

		$rsPageI18n = $this->okt->module('Pages')->pages->getPageL10n($this->aPageData['post']['id']);

		foreach ($this->okt->languages->list as $aLanguage)
		{
			while ($rsPageI18n->fetch())
			{
				if ($rsPageI18n->language == $aLanguage['code'])
				{
					$this->aPageData['locales'][$aLanguage['code']]['title'] = $rsPageI18n->title;
					$this->aPageData['locales'][$aLanguage['code']]['subtitle'] = $rsPageI18n->subtitle;
					$this->aPageData['locales'][$aLanguage['code']]['content'] = $rsPageI18n->content;

					if ($this->okt->module('Pages')->config->enable_metas)
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
		if ($this->okt->module('Pages')->config->images['enable'])
		{
			$this->aPageData['images'] = $rsPage->getImagesInfo();
		}

		# Fichiers
		if ($this->okt->module('Pages')->config->files['enable'])
		{
			$this->aPageData['files'] = $rsPage->getFilesInfo();
		}

		# Permissions
		if ($this->okt->module('Pages')->config->enable_group_perms)
		{
			$this->aPageData['perms'] = $this->okt->module('Pages')->pages->getPagePermissions($this->aPageData['post']['id']);
		}

		# switch page status
		if ($this->request->query->has('switch_status'))
		{
			try
			{
				$this->okt->module('Pages')->pages->switchPageStatus($this->aPageData['post']['id']);

				# log admin
				$this->okt->logAdmin->info(array(
					'code' => 32,
					'component' => 'pages',
					'message' => 'page #' . $this->aPageData['post']['id']
				));

				return $this->redirect($this->generateUrl('Pages_post', array(
					'page_id' => $this->aPageData['post']['id']
				)));
			}
			catch (\Exception $e)
			{
				$this->okt->error->set($e->getMessage());
			}
		}

		# suppression d'une image
		if ($this->request->query->has('delete_image'))
		{
			$this->okt->module('Pages')->pages->getImageUpload()->delete($this->aPageData['post']['id'], $this->request->query->get('delete_image'));

			# log admin
			$this->okt->logAdmin->info(array(
				'code' => 41,
				'component' => 'pages',
				'message' => 'page #' . $this->aPageData['post']['id']
			));

			$this->okt->page->flash->success(__('m_pages_page_updated'));

			return $this->redirect($this->generateUrl('Pages_post', array(
				'page_id' => $this->aPageData['post']['id']
			)));
		}

		# suppression d'un fichier
		if ($this->request->query->has('delete_file'))
		{
			$this->okt->module('Pages')->deleteFile($this->aPageData['post']['id'], $this->request->query->get('delete_file'));

			# log admin
			$this->okt->logAdmin->info(array(
				'code' => 41,
				'component' => 'pages',
				'message' => 'page #' . $this->aPageData['post']['id']
			));

			$this->okt->page->flash->success(__('m_pages_page_updated'));

			return $this->redirect($this->generateUrl('Pages_post', array(
				'page_id' => $this->aPageData['post']['id']
			)));
		}

		if ($this->populateDataFromPost())
		{
			try
			{
				# -- TRIGGER MODULE PAGES : beforePageUpdate
				$this->okt->module('Pages')->triggers->callTrigger('beforePageUpdate', $this->aPageData);

				$this->okt->module('Pages')->pages->updPage($this->aPageData['cursor'], $this->aPageData['locales'], $this->aPageData['perms']);

				# -- TRIGGER MODULE PAGES : afterPageUpdate
				$this->okt->module('Pages')->triggers->callTrigger('afterPageUpdate', $this->aPageData);

				# log admin
				$this->okt->logAdmin->info(array(
					'code' => 41,
					'component' => 'pages',
					'message' => 'page #' . $this->aPageData['post']['id']
				));

				$this->okt->page->flash->success(__('m_pages_page_updated'));

				return $this->redirect($this->generateUrl('Pages_post', array(
					'page_id' => $this->aPageData['post']['id']
				)));
			}
			catch (\Exception $e)
			{
				$this->okt->error->set($e->getMessage());
			}
		}

		return $this->display();
	}

	protected function init()
	{
		# Chargement des locales
		$this->okt->l10n->loadFile(__DIR__ . '/../../Locales/%s/admin.post');

		# Données de la page
		$this->aPageData = new ArrayObject();

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

			if ($this->okt->module('Pages')->config->enable_metas)
			{
				$this->aPageData['locales'][$aLanguage['code']]['title_seo'] = '';
				$this->aPageData['locales'][$aLanguage['code']]['title_tag'] = '';
				$this->aPageData['locales'][$aLanguage['code']]['slug'] = '';
				$this->aPageData['locales'][$aLanguage['code']]['meta_description'] = '';
				$this->aPageData['locales'][$aLanguage['code']]['meta_keywords'] = '';
			}
		}

		$this->aPageData['perms'] = array(
			0
		);
		$this->aPageData['images'] = array();
		$this->aPageData['files'] = array();

		$rsPage = null;
		$rsPageI18n = null;

		# -- TRIGGER MODULE PAGES : adminPostInit
		$this->okt->module('Pages')->triggers->callTrigger('adminPostInit', $this->aPageData, $rsPage, $rsPageI18n);
	}

	protected function populateDataFromPost()
	{
		if (! $this->request->request->has('sended'))
		{
			return false;
		}

		$this->aPageData['post']['category_id'] = $this->request->request->getInt('p_category_id');
		$this->aPageData['post']['active'] = $this->request->request->getInt('p_active');
		$this->aPageData['post']['tpl'] = $this->request->request->get('p_tpl');

		foreach ($this->okt->languages->list as $aLanguage)
		{
			$this->aPageData['locales'][$aLanguage['code']]['title'] = $this->request->request->get('p_title[' . $aLanguage['code'] . ']', null, true);
			$this->aPageData['locales'][$aLanguage['code']]['subtitle'] = $this->request->request->get('p_subtitle[' . $aLanguage['code'] . ']', null, true);
			$this->aPageData['locales'][$aLanguage['code']]['content'] = $this->request->request->get('p_content[' . $aLanguage['code'] . ']', null, true);

			if ($this->okt->module('Pages')->config->enable_metas)
			{
				$this->aPageData['locales'][$aLanguage['code']]['title_seo'] = $this->request->request->get('p_title_seo[' . $aLanguage['code'] . ']', null, true);
				$this->aPageData['locales'][$aLanguage['code']]['title_tag'] = $this->request->request->get('p_title_tag[' . $aLanguage['code'] . ']', null, true);
				$this->aPageData['locales'][$aLanguage['code']]['meta_description'] = $this->request->request->get('p_meta_description[' . $aLanguage['code'] . ']', null, true);
				$this->aPageData['locales'][$aLanguage['code']]['meta_keywords'] = $this->request->request->get('p_meta_keywords[' . $aLanguage['code'] . ']', null, true);
				$this->aPageData['locales'][$aLanguage['code']]['slug'] = $this->request->request->get('p_slug[' . $aLanguage['code'] . ']', null, true);
			}
		}

		$this->aPageData['perms'] = $this->request->request->get('perms', array());

		# -- TRIGGER MODULE PAGES : adminPopulateData
		$this->okt->module('Pages')->triggers->callTrigger('adminPopulateData', $this->aPageData);

		# vérification des données avant modification dans la BDD
		if ($this->okt->module('Pages')->pages->checkPostData($this->aPageData))
		{
			$this->aPageData['cursor'] = $this->okt->module('Pages')->pages->openPageCursor($this->aPageData['post']);

			return true;
		}

		return false;
	}

	protected function display()
	{
		# Récupération de la liste complète des rubriques
		$rsCategories = null;
		if ($this->okt->module('Pages')->config->categories['enable'])
		{
			$rsCategories = $this->okt->module('Pages')->categories->getCategories(array(
				'active' => 2,
				'language' => $this->okt->user->language
			));
		}

		# Liste des templates utilisables
		$oTemplatesItem = new TemplatesSet($this->okt, $this->okt->module('Pages')->config->templates['item'], 'pages/item', 'item');
		$aTplChoices = array_merge(array(
			'&nbsp;' => null
		), $oTemplatesItem->getUsablesTemplatesForSelect($this->okt->module('Pages')->config->templates['item']['usables']));

		# Récupération de la liste des groupes si les permissions sont activées
		$aGroups = null;
		if ($this->okt->module('Pages')->config->enable_group_perms)
		{
			$aGroups = $this->okt->module('Pages')->pages->getUsersGroupsForPerms(false, true);
		}

		# Construction des onglets
		$this->aPageData['tabs'] = new ArrayObject();

		# onglet contenu
		$this->aPageData['tabs'][10] = array(
			'id' => 'tab-content',
			'title' => __('m_pages_page_tab_content'),
			'content' => $this->renderView('Pages/Admin/Templates/Post/Tabs/Content', array(
				'aPageData' => $this->aPageData
			))
		);

		# onglet images
		if ($this->okt->module('Pages')->config->images['enable'])
		{
			$this->aPageData['tabs'][20] = array(
				'id' => 'tab-images',
				'title' => __('m_pages_page_tab_images'),
				'content' => $this->renderView('Pages/Admin/Templates/Post/Tabs/Images', array(
					'aPageData' => $this->aPageData
				))
			);
		}

		# onglet fichiers
		if ($this->okt->module('Pages')->config->files['enable'])
		{
			$this->aPageData['tabs'][30] = array(
				'id' => 'tab-files',
				'title' => __('m_pages_page_tab_files'),
				'content' => $this->renderView('Pages/Admin/Templates/Post/Tabs/Files', array(
					'aPageData' => $this->aPageData
				))
			);
		}

		# onglet options
		$this->aPageData['tabs'][40] = array(
			'id' => 'tab-options',
			'title' => __('m_pages_page_tab_options'),
			'content' => $this->renderView('Pages/Admin/Templates/Post/Tabs/Options', array(
				'rsCategories' => $rsCategories,
				'aPageData' => $this->aPageData
			))
		);

		# onglet seo
		if ($this->okt->module('Pages')->config->enable_metas)
		{
			$this->aPageData['tabs'][50] = array(
				'id' => 'tab-seo',
				'title' => __('m_pages_page_tab_seo'),
				'content' => $this->renderView('Pages/Admin/Templates/Post/Tabs/Seo', array(
					'aPageData' => $this->aPageData
				))
			);
		}

		# -- TRIGGER MODULE PAGES : adminPostBuildTabs
		$this->okt->module('Pages')->triggers->callTrigger('adminPostBuildTabs', $this->aPageData);

		$this->aPageData['tabs']->ksort();

		return $this->render('Pages/Admin/Templates/Post/Page', array(
			'aPageData' => $this->aPageData,
			'rsCategories' => $rsCategories,
			'aTplChoices' => $aTplChoices,
			'aGroups' => $aGroups
		));
	}
}
