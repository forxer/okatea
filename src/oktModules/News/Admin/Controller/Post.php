<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Module\News\Admin\Controller;

use Tao\Admin\Controller;
use Tao\Themes\TemplatesSet;

class Post extends Controller
{
	protected $aPostData;

	protected $aPermissions;

	public function add()
	{
		$this->init();

		if ($this->populateDataFromPost())
		{
			try
			{
				# -- TRIGGER MODULE NEWS : beforePostCreate
				$this->okt->News->triggers->callTrigger('beforePostCreate', $this->okt, $this->aPostData);

				$this->aPostData['post']['id'] = $this->okt->News->addPost($this->aPostData['cursor'], $this->aPostData['locales'], $this->aPostData['perms']);

				# -- TRIGGER MODULE NEWS : afterPostCreate
				$this->okt->News->triggers->callTrigger('afterPostCreate', $this->okt, $this->aPostData);

				# log admin
				$this->okt->logAdmin->info(array(
					'code' => 40,
					'component' => 'news',
					'message' => 'post #'.$this->aPostData['post']['id']
				));

				$this->page->flash->success(__('m_news_post_added'));

				return $this->redirect($this->generateUrl('News_post', array('post_id' => $this->aPostData['post']['id'])));
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

		$this->aPostData['post']['id'] = $this->request->attributes->getInt('post_id');

		$rsPost = $this->okt->News->getPostsRecordset(array(
			'id' => $this->aPostData['post']['id']
		));

		if (null === $this->aPostData['post']['id'] || $rsPost->isEmpty())
		{
			$this->page->flash->error(sprintf(__('m_news_post_%s_not_exists'), $this->aPostData['post']['id']));

			return $this->serve404();
		}

		$this->aPermissions['bCanEditPost'] = $rsPost->isEditable();
		$this->aPermissions['bCanPublish'] = $rsPost->isPublishable();
		$this->aPermissions['bCanDelete'] = $rsPost->isDeletable();

		$this->aPostData['post']['category_id'] = $rsPost->category_id;
		$this->aPostData['post']['active'] = $rsPost->active;
		$this->aPostData['post']['selected'] = $rsPost->selected;
		$this->aPostData['post']['tpl'] = $rsPost->tpl;
		$this->aPostData['post']['created_at'] = $rsPost->created_at;
		$this->aPostData['post']['updated_at'] = $rsPost->updated_at;

		$iPotsTs = strtotime($rsPost->created_at);

		$this->aPostData['extra']['date'] = date('d-m-Y', $iPotsTs);
		$this->aPostData['extra']['hours'] = date('H', $iPotsTs);
		$this->aPostData['extra']['minutes'] = date('i', $iPotsTs);

		$rsPostI18n = $this->okt->News->getPostI18n($this->aPostData['post']['id']);

		foreach ($this->okt->languages->list as $aLanguage)
		{
			while ($rsPostI18n->fetch())
			{
				if ($rsPostI18n->language == $aLanguage['code'])
				{
					$this->aPostData['locales'][$aLanguage['code']]['title'] = $rsPostI18n->title;
					$this->aPostData['locales'][$aLanguage['code']]['subtitle'] = $rsPostI18n->subtitle;
					$this->aPostData['locales'][$aLanguage['code']]['content'] = $rsPostI18n->content;

					if ($this->okt->News->config->enable_metas)
					{
						$this->aPostData['locales'][$aLanguage['code']]['title_seo'] = $rsPostI18n->title_seo;
						$this->aPostData['locales'][$aLanguage['code']]['title_tag'] = $rsPostI18n->title_tag;
						$this->aPostData['locales'][$aLanguage['code']]['slug'] = $rsPostI18n->slug;
						$this->aPostData['locales'][$aLanguage['code']]['meta_description'] = $rsPostI18n->meta_description;
						$this->aPostData['locales'][$aLanguage['code']]['meta_keywords'] = $rsPostI18n->meta_keywords;
					}
				}
			}
		}

		# Images
		if ($this->okt->News->config->images['enable']) {
			$this->aPostData['images'] = $rsPost->getImagesInfo();
		}

		# Fichiers
		if ($this->okt->News->config->files['enable']) {
			$this->aPostData['files'] = $rsPost->getFilesInfo();
		}

		# Permissions
		if ($this->okt->News->canUsePerms()) {
			$this->aPostData['perms'] = $this->okt->News->getPostPermissions($this->aPostData['post']['id']);
		}

		# switch post status
		if ($this->request->query->has('switch_status') && $this->aPermissions['bCanEditPost'])
		{
			try
			{
				$this->okt->News->switchPostStatus($this->aPostData['post']['id']);

				# log admin
				$this->okt->logAdmin->info(array(
					'code' => 32,
					'component' => 'news',
					'message' => 'post #'.$this->aPostData['post']['id']
				));

				return $this->redirect($this->generateUrl('News_post', array('post_id' => $this->aPostData['post']['id'])));
			}
			catch (Exception $e) {
				$this->okt->error->set($e->getMessage());
			}
		}

		# publication de l'article
		if ($this->request->query->has('publish') && $this->aPermissions['bCanPublish'])
		{
			$this->okt->News->publishPost($this->aPostData['post']['id']);

			# log admin
			$this->okt->logAdmin->info(array(
				'code' => 41,
				'component' => 'news',
				'message' => 'post #'.$this->aPostData['post']['id']
			));

			$this->page->flash->success(__('m_news_post_published'));

			return $this->redirect($this->generateUrl('News_post', array('post_id' => $this->aPostData['post']['id'])));
		}

		# suppression d'une image
		if ($this->request->query->has('delete_image') && $this->aPermissions['bCanEditPost'])
		{
			$this->okt->News->deleteImage($this->aPostData['post']['id'], $this->request->query->get('delete_image'));

			# log admin
			$this->okt->logAdmin->info(array(
				'code' => 41,
				'component' => 'news',
				'message' => 'post #'.$this->aPostData['post']['id']
			));

			return $this->redirect($this->generateUrl('News_post', array('post_id' => $this->aPostData['post']['id'])));
		}

		# suppression d'un fichier
		if ($this->request->query->has('delete_file') && $this->aPermissions['bCanEditPost'])
		{
			$this->okt->News->deleteFile($this->aPostData['post']['id'], $this->request->query->get('delete_file'));

			# log admin
			$this->okt->logAdmin->info(array(
				'code' => 41,
				'component' => 'news',
				'message' => 'post #'.$this->aPostData['post']['id']
			));

			return $this->redirect($this->generateUrl('News_post', array('post_id' => $this->aPostData['post']['id'])));
		}

		if ($this->populateDataFromPost())
		{
			try
			{
				# -- TRIGGER MODULE NEWS : beforePostUpdate
				$this->okt->News->triggers->callTrigger('beforePostUpdate', $this->okt, $this->aPostData);

				$this->okt->News->updPost($this->aPostData['cursor'], $this->aPostData['locales'], $this->aPostData['perms']);

				# -- TRIGGER MODULE NEWS : afterPostUpdate
				$this->okt->News->triggers->callTrigger('afterPostUpdate', $this->okt, $this->aPostData);

				# log admin
				$this->okt->logAdmin->info(array(
					'code' => 41,
					'component' => 'news',
					'message' => 'post #'.$this->aPostData['post']['id']
				));

				$this->page->flash->success(__('m_news_post_updated'));

				return $this->redirect($this->generateUrl('News_post', array('post_id' => $this->aPostData['post']['id'])));
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
		$this->okt->l10n->loadFile(__DIR__.'/../../Locales/'.$this->okt->user->language.'/admin.post');

		$this->aPermissions = array(
			'bCanViewPage' => true,
			'bCanEditPost' => ($this->okt->checkPerm('news_usage') || $this->okt->checkPerm('news_contentadmin')),
			'bCanPublish' => ($this->okt->checkPerm('news_publish') || $this->okt->checkPerm('news_contentadmin')),
			'bCanDelete' => ($this->okt->checkPerm('news_delete') || $this->okt->checkPerm('news_contentadmin'))
		);

		$this->aPermissions['bCanDelete'] = ($this->okt->checkPerm('news_delete') || $this->okt->checkPerm('news_contentadmin'));

		# Données de l'article
		$this->aPostData = new \ArrayObject();

		$this->aPostData['post'] = array();
		$this->aPostData['post']['id'] = null;

		$this->aPostData['post']['category_id'] = 0;
		$this->aPostData['post']['active'] = 1;
		$this->aPostData['post']['selected'] = 0;
		$this->aPostData['post']['tpl'] = '';
		$this->aPostData['post']['created_at'] = '';
		$this->aPostData['post']['updated_at'] = '';

		# If user can't publish
		if (!$this->aPermissions['bCanPublish']) {
			$this->aPostData['post']['active'] = 2;
		}

		$this->aPostData['extra'] = array();
		$this->aPostData['extra']['date'] = '';
		$this->aPostData['extra']['hours'] = '';
		$this->aPostData['extra']['minutes'] = '';

		$this->aPostData['locales'] = array();

		foreach ($this->okt->languages->list as $aLanguage)
		{
			$this->aPostData['locales'][$aLanguage['code']] = array();

			$this->aPostData['locales'][$aLanguage['code']]['title'] = '';
			$this->aPostData['locales'][$aLanguage['code']]['subtitle'] = '';
			$this->aPostData['locales'][$aLanguage['code']]['content'] = '';

			if ($this->okt->News->config->enable_metas)
			{
				$this->aPostData['locales'][$aLanguage['code']]['title_seo'] = '';
				$this->aPostData['locales'][$aLanguage['code']]['title_tag'] = '';
				$this->aPostData['locales'][$aLanguage['code']]['slug'] = '';
				$this->aPostData['locales'][$aLanguage['code']]['meta_description'] = '';
				$this->aPostData['locales'][$aLanguage['code']]['meta_keywords'] = '';
			}
		}

		$this->aPostData['perms'] = array(0);
		$this->aPostData['images'] = array();
		$this->aPostData['files'] = array();

		$rsPost = null;
		$rsPostI18n = null;

		# -- TRIGGER MODULE NEWS : adminPostInit
		$this->okt->News->triggers->callTrigger('adminPostInit', $this->okt, $this->aPostData, $rsPost, $rsPostI18n);
	}

	protected function populateDataFromPost()
	{
		if (!$this->request->request->has('sended') || !$this->aPermissions['bCanEditPost']) {
			return false;
		}

		$this->aPostData['post']['category_id'] = $this->request->request->getInt('p_category_id');
		$this->aPostData['post']['active'] = $this->request->request->getInt('p_active');
		$this->aPostData['post']['selected'] = $this->request->request->getInt('p_selected');
		$this->aPostData['post']['tpl'] = $this->request->request->get('p_tpl');

		$this->aPostData['extra']['date'] = $this->request->request->get('p_date');
		$this->aPostData['extra']['hours'] = $this->request->request->getInt('p_hours');
		$this->aPostData['extra']['minutes'] = $this->request->request->getInt('p_minutes');

		if (!empty($this->aPostData['extra']['date']))
		{
			$this->aPostData['post']['created_at'] = $this->aPostData['extra']['date'].' '.
					(!empty($this->aPostData['extra']['hours']) ? $this->aPostData['extra']['hours'] : date('H')).':'.
					(!empty($this->aPostData['extra']['minutes']) ? $this->aPostData['extra']['minutes'] : date('i'));
		}

		foreach ($this->okt->languages->list as $aLanguage)
		{
			$this->aPostData['locales'][$aLanguage['code']]['title'] = $this->request->request->get('p_title['.$aLanguage['code'].']', null, true);
			$this->aPostData['locales'][$aLanguage['code']]['subtitle'] = $this->request->request->get('p_subtitle['.$aLanguage['code'].']', null, true);
			$this->aPostData['locales'][$aLanguage['code']]['content'] = $this->request->request->get('p_content['.$aLanguage['code'].']', null, true);

			if ($this->okt->News->config->enable_metas)
			{
				$this->aPostData['locales'][$aLanguage['code']]['title_seo'] = $this->request->request->get('p_title_seo['.$aLanguage['code'].']', null, true);
				$this->aPostData['locales'][$aLanguage['code']]['title_tag'] = $this->request->request->get('p_title_tag['.$aLanguage['code'].']', null, true);
				$this->aPostData['locales'][$aLanguage['code']]['slug'] = $this->request->request->get('p_slug['.$aLanguage['code'].']', null, true);
				$this->aPostData['locales'][$aLanguage['code']]['meta_description'] = $this->request->request->get('p_meta_description['.$aLanguage['code'].']', null, true);
				$this->aPostData['locales'][$aLanguage['code']]['meta_keywords'] = $this->request->request->get('p_meta_keywords['.$aLanguage['code'].']', null, true);
			}
		}

		$this->aPostData['perms'] = $this->request->request->get('perms', array());

		# -- TRIGGER MODULE NEWS : adminPopulateData
		$this->okt->News->triggers->callTrigger('adminPopulateData', $this->okt, $this->aPostData);

		# vérification des données avant modification dans la BDD
		if ($this->okt->News->checkPostData($this->aPostData['post'], $this->aPostData['locales'], $this->aPostData['perms']))
		{
			$this->aPostData['cursor'] = $this->okt->News->openPostCursor($this->aPostData['post']);

			return true;
		}

		return false;
	}

	protected function display()
	{
		# Récupération de la liste complète des rubriques
		$rsCategories = null;
		if ($this->okt->News->config->categories['enable'])
		{
			$rsCategories = $this->okt->News->categories->getCategories(array(
				'active' => 2,
				'language' => $this->okt->user->language
			));
		}

		# Liste des templates utilisables
		$oTemplatesItem = new TemplatesSet($this->okt, $this->okt->News->config->templates['item'], 'news/item', 'item');
		$aTplChoices = array_merge(
			array('&nbsp;' => null),
			$oTemplatesItem->getUsablesTemplatesForSelect($this->okt->News->config->templates['item']['usables'])
		);

		# Récupération de la liste des groupes si les permissions sont activées
		$aGroups = null;
		if ($this->okt->News->canUsePerms()) {
			$aGroups = $this->okt->News->getUsersGroupsForPerms(false,true);
		}

		# Construction des onglets
		$this->aPostData['tabs'] = new \ArrayObject;

		# onglet contenu
		$this->aPostData['tabs'][10] = array(
			'id' => 'tab-content',
			'title' => __('m_news_post_tab_content'),
			'content' => $this->renderView('News/Admin/Templates/Post/Tabs/Content', array(
				'aPostData' => $this->aPostData
			))
		);

		# onglet images
		if ($this->okt->News->config->images['enable'])
		{
			$this->aPostData['tabs'][20] = array(
				'id' => 'tab-images',
				'title' => __('m_news_post_tab_images'),
				'content' => $this->renderView('News/Admin/Templates/Post/Tabs/Images', array(
					'aPermissions' 	=> $this->aPermissions,
					'aPostData' 	=> $this->aPostData
				))
			);
		}

		# onglet fichiers
		if ($this->okt->News->config->files['enable'])
		{
			$this->aPostData['tabs'][30] = array(
				'id' => 'tab-files',
				'title' => __('m_news_post_tab_files'),
				'content' => $this->renderView('News/Admin/Templates/Post/Tabs/Files', array(
					'aPermissions' 	=> $this->aPermissions,
					'aPostData' 	=> $this->aPostData
				))
			);
		}

		# onglet options
		$this->aPostData['tabs'][40] = array(
			'id' => 'tab-options',
			'title' => __('m_news_post_tab_options'),
			'content' => $this->renderView('News/Admin/Templates/Post/Tabs/Options', array(
				'rsCategories' => $rsCategories,
				'aPermissions' 	=> $this->aPermissions,
				'aPostData' => $this->aPostData
			))
		);

		# onglet seo
		if ($this->okt->News->config->enable_metas)
		{
			$this->aPostData['tabs'][50] = array(
				'id' => 'tab-seo',
				'title' => __('m_news_post_tab_seo'),
				'content' => $this->renderView('News/Admin/Templates/Post/Tabs/Seo', array(
					'aPostData' => $this->aPostData
				))
			);
		}

		# -- TRIGGER MODULE NEWS : adminPostBuildTabs
		$this->okt->News->triggers->callTrigger('adminPostBuildTabs', $this->okt, $this->aPostData);

		$this->aPostData['tabs']->ksort();

		return $this->render('News/Admin/Templates/Post/Page', array(
			'aPermissions' 	=> $this->aPermissions,
			'aPostData' 	=> $this->aPostData,
			'rsCategories' 	=> $rsCategories,
			'aTplChoices' 	=> $aTplChoices,
			'aGroups' 		=> $aGroups
		));
	}
}