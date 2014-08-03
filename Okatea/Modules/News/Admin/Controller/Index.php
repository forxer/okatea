<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\News\Admin\Controller;

use Okatea\Admin\Controller;
use Okatea\Admin\Pager;

class Index extends Controller
{

	public function page()
	{
		if (! $this->okt->checkPerm('news_usage') && ! $this->okt->checkPerm('news_contentadmin'))
		{
			return $this->serve401();
		}
		
		if (($json = $this->getPostsJson()) !== false)
		{
			return $json;
		}
		
		# Chargement des locales
		$this->okt['l10n']->loadFile(__DIR__ . '/../../Locales/%s/admin.list');
		
		# initialisation des filtres
		$this->okt->module('News')->filtersStart('admin');
		
		# Ré-initialisation filtres
		if (($action = $this->initFilters()) !== false)
		{
			return $action;
		}
		
		# Suppression d'un article
		if (($action = $this->deletePost()) !== false)
		{
			return $action;
		}
		
		# Switch post statut
		if (($action = $this->switchPostStatus()) !== false)
		{
			return $action;
		}
		
		# Switch article selection
		if (($action = $this->switchPostSelect()) !== false)
		{
			return $action;
		}
		
		# Sélectionne un article
		if (($action = $this->selectPost()) !== false)
		{
			return $action;
		}
		
		# Déselectionne un article
		if (($action = $this->unselectPost()) !== false)
		{
			return $action;
		}
		
		# Publication d'un article
		if (($action = $this->publishPost()) !== false)
		{
			return $action;
		}
		
		# Traitements par lots
		if (($action = $this->batches()) !== false)
		{
			return $action;
		}
		
		# Publication des articles différés
		$this->okt->module('News')->publishScheduledPosts();
		
		# Initialisation des filtres
		$aParams = array();
		
		if (! $this->okt->checkPerm('news_contentadmin') && ! $this->okt->checkPerm('news_show_all'))
		{
			$aParams['user_id'] = $this->okt->user->id;
		}
		
		$sSearch = $this->okt['request']->query->get('search');
		
		if ($sSearch)
		{
			$aParams['search'] = $sSearch;
		}
		
		$this->okt->module('News')->filters->setPostsParams($aParams);
		
		# Création des filtres
		$this->okt->module('News')->filters->getFilters();
		
		# Initialisation de la pagination
		$iNumFilteredPosts = $this->okt->module('News')->getPostsCount($aParams);
		
		$oPager = new Pager($this->okt, $this->okt->module('News')->filters->params->page, $iNumFilteredPosts, $this->okt->module('News')->filters->params->nb_per_page);
		
		$iNumPages = $oPager->getNbPages();
		
		$this->okt->module('News')->filters->normalizePage($iNumPages);
		
		$aParams['limit'] = (($this->okt->module('News')->filters->params->page - 1) * $this->okt->module('News')->filters->params->nb_per_page) . ',' . $this->okt->module('News')->filters->params->nb_per_page;
		
		# Récupération des articles
		$rsPosts = $this->okt->module('News')->getPosts($aParams);
		
		# Liste des groupes si les permissions sont activées
		$aGroups = null;
		if ($this->okt->module('News')->config->enable_group_perms)
		{
			$aGroups = $this->okt->module('News')->getUsersGroupsForPerms(true, true);
		}
		
		# Tableau de choix d'actions pour le traitement par lot
		$aActionsChoices = array(
			'&nbsp;' => null,
			__('m_news_list_status') => array(
				__('c_c_action_display') => 'show',
				__('c_c_action_hide') => 'hide'
			)
		);
		
		if ($this->okt->checkPerm('news_publish') || $this->okt->checkPerm('news_contentadmin'))
		{
			$aActionsChoices[__('m_news_list_status')][__('c_c_action_publish')] = 'publish';
		}
		
		$aActionsChoices[__('m_news_list_mark')] = array(
			__('c_c_action_select') => 'selected',
			__('c_c_action_deselect') => 'unselected'
		);
		
		if ($this->okt->checkPerm('news_delete') || $this->okt->checkPerm('news_contentadmin'))
		{
			$aActionsChoices[__('c_c_action_Delete')][__('c_c_action_delete')] = 'delete';
		}
		
		return $this->render('News/Admin/Templates/Index', array(
			'rsPosts' => $rsPosts,
			'aGroups' => $aGroups,
			'aActionsChoices' => $aActionsChoices,
			'iNumFilteredPosts' => $iNumFilteredPosts,
			'iNumPages' => $iNumPages,
			'oPager' => $oPager,
			'sSearch' => $sSearch
		));
	}

	protected function getPostsJson()
	{
		$json = $this->okt['request']->query->get('json');
		$term = $this->okt['request']->query->get('term');
		
		if (! $json || ! $term || ! $this->okt['request']->isXmlHttpRequest())
		{
			return false;
		}
		
		$rsPosts = $this->okt->module('News')->getPostsRecordset(array(
			'language' => $this->okt->user->language,
			'search' => $term
		));
		
		$aResults = array();
		while ($rsPosts->fetch())
		{
			$aResults[$rsPosts->title] = $rsPosts->title;
		}
		
		return $this->jsonResponse(array_unique($aResults));
	}

	protected function initFilters()
	{
		if ($this->okt['request']->query->has('init_filters'))
		{
			$this->okt->module('News')->filters->initFilters();
			
			return $this->redirect($this->generateUrl('News_index'));
		}
		
		return false;
	}

	protected function deletePost()
	{
		$iPostId = $this->okt['request']->query->getInt('delete');
		
		if (! $iPostId || ! $this->okt->checkPerm('news_delete'))
		{
			return false;
		}
		
		if ($this->okt->module('News')->deletePost($iPostId))
		{
			$this->okt->logAdmin->warning(array(
				'code' => 42,
				'component' => 'news',
				'message' => 'post #' . $iPostId
			));
			
			$this->okt['flash']->success(__('m_news_list_post_deleted'));
			
			return $this->redirect($this->generateUrl('News_index'));
		}
		
		return false;
	}

	protected function switchPostStatus()
	{
		$iPostId = $this->okt['request']->query->getInt('switch_status');
		
		if (! $iPostId)
		{
			return false;
		}
		
		if ($this->okt->module('News')->switchPostStatus($iPostId))
		{
			$this->okt->logAdmin->info(array(
				'code' => 32,
				'component' => 'news',
				'message' => 'post #' . $iPostId
			));
			
			return $this->redirect($this->generateUrl('News_index'));
		}
		
		return false;
	}

	protected function switchPostSelect()
	{
		$iPostId = $this->okt['request']->query->getInt('switch_selected');
		
		if (! $iPostId)
		{
			return false;
		}
		
		if ($this->okt->module('News')->switchPostSelected($iPostId))
		{
			$this->okt->logAdmin->info(array(
				'code' => 41,
				'component' => 'news',
				'message' => 'post #' . $iPostId
			));
			
			return $this->redirect($this->generateUrl('News_index'));
		}
		
		return false;
	}

	protected function selectPost()
	{
		$iPostId = $this->okt['request']->query->getInt('select');
		
		if (! $iPostId)
		{
			return false;
		}
		
		if ($this->okt->module('News')->setPostSelected($iPostId, true))
		{
			$this->okt->logAdmin->info(array(
				'code' => 41,
				'component' => 'news',
				'message' => 'post #' . $iPostId
			));
			
			$this->okt['flash']->success(__('m_news_list_post_selected'));
			
			return $this->redirect($this->generateUrl('News_index'));
		}
		
		return false;
	}

	protected function unselectPost()
	{
		$iPostId = $this->okt['request']->query->getInt('deselect');
		
		if (! $iPostId)
		{
			return false;
		}
		
		if ($this->okt->module('News')->setPostSelected($iPostId, false))
		{
			$this->okt->logAdmin->info(array(
				'code' => 41,
				'component' => 'news',
				'message' => 'post #' . $iPostId
			));
			
			$this->okt['flash']->success(__('m_news_list_post_deselected'));
			
			return $this->redirect($this->generateUrl('News_index'));
		}
		
		return false;
	}

	protected function publishPost()
	{
		$iPostId = $this->okt['request']->query->getInt('publish');
		
		if (! $iPostId)
		{
			return false;
		}
		
		if ($this->okt->module('News')->publishPost($iPostId))
		{
			$this->okt->logAdmin->info(array(
				'code' => 30,
				'component' => 'news',
				'message' => 'post #' . $iPostId
			));
			
			$this->okt['flash']->success(__('m_news_list_post_published'));
			
			return $this->redirect($this->generateUrl('News_index'));
		}
		
		return false;
	}

	protected function batches()
	{
		$sAction = $this->okt['request']->request->get('action');
		$aPostsId = $this->okt['request']->request->get('posts');
		
		if (! $sAction || ! $aPostsId || ! is_array($aPostsId))
		{
			return false;
		}
		
		$aPostsId = array_map('intval', $aPostsId);
		
		if ($sAction === 'show')
		{
			foreach ($aPostsId as $iPostId)
			{
				if ($this->okt->module('News')->showPost($iPostId, 1))
				{
					$this->okt->logAdmin->info(array(
						'code' => 30,
						'component' => 'news',
						'message' => 'post #' . $iPostId
					));
				}
			}
		}
		elseif ($sAction === 'hide')
		{
			foreach ($aPostsId as $iPostId)
			{
				if ($this->okt->module('News')->hidePost($iPostId))
				{
					$this->okt->logAdmin->info(array(
						'code' => 31,
						'component' => 'news',
						'message' => 'post #' . $iPostId
					));
				}
			}
		}
		elseif ($sAction === 'publish')
		{
			foreach ($aPostsId as $iPostId)
			{
				if ($this->okt->module('News')->publishPost($iPostId))
				{
					$this->okt->logAdmin->info(array(
						'code' => 30,
						'component' => 'news',
						'message' => 'post #' . $iPostId
					));
				}
			}
			
			$this->okt['flash']->success(__('m_news_list_posts_published'));
		}
		elseif ($sAction === 'selected')
		{
			foreach ($aPostsId as $iPostId)
			{
				if ($this->okt->module('News')->setPostSelected($iPostId, 1))
				{
					$this->okt->logAdmin->info(array(
						'code' => 41,
						'component' => 'news',
						'message' => 'post #' . $iPostId
					));
				}
			}
			
			$this->okt['flash']->success(__('m_news_list_posts_selected'));
		}
		elseif ($sAction === 'unselected')
		{
			foreach ($aPostsId as $iPostId)
			{
				if ($this->okt->module('News')->setPostSelected($iPostId, 0))
				{
					$this->okt->logAdmin->info(array(
						'code' => 41,
						'component' => 'news',
						'message' => 'post #' . $iPostId
					));
				}
			}
			
			$this->okt['flash']->success(__('m_news_list_posts_deselected'));
		}
		elseif ($sAction === 'delete' && $this->okt->checkPerm('news_delete'))
		{
			foreach ($aPostsId as $iPostId)
			{
				if ($this->okt->module('News')->deletePost($iPostId))
				{
					$this->okt->logAdmin->warning(array(
						'code' => 42,
						'component' => 'news',
						'message' => 'post #' . $iPostId
					));
				}
			}
			
			$this->okt['flash']->success(__('m_news_list_posts_deleted'));
		}
		
		return $this->redirect($this->generateUrl('News_index'));
	}
}
