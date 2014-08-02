<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Pages\Admin\Controller;

use Okatea\Admin\Controller;
use Okatea\Admin\Pager;

class Index extends Controller
{

	public function page()
	{
		if (! $this->okt->checkPerm('pages_usage'))
		{
			return $this->serve401();
		}

		# json pages list for autocomplete
		if (($json = $this->getPagesJson()) !== false)
		{
			return $json;
		}

		# Chargement des locales
		$this->okt->l10n->loadFile(__DIR__ . '/../../Locales/%s/admin.list');

		# initialisation des filtres
		$this->okt->module('Pages')->filtersStart('admin');

		# Ré-initialisation filtres
		if (($action = $this->initFilters()) !== false)
		{
			return $action;
		}

		# Delete page
		if (($action = $this->deletePost()) !== false)
		{
			return $action;
		}

		# Switch page statut
		if (($action = $this->switchPostStatus()) !== false)
		{
			return $action;
		}

		# Traitements par lots
		if (($action = $this->batches()) !== false)
		{
			return $action;
		}

		# Initialisation des filtres
		$aParams = array();

		$sSearch = $this->request->query->get('search');

		if ($sSearch)
		{
			$aParams['search'] = $sSearch;
		}

		$this->okt->module('Pages')->filters->setPagesParams($aParams);

		# Création des filtres
		$this->okt->module('Pages')->filters->getFilters();

		# Initialisation de la pagination
		$iNumFilteredPosts = $this->okt->module('Pages')->pages->getPagesCount($aParams);

		$oPager = new Pager($this->okt, $this->okt->module('Pages')->filters->params->page, $iNumFilteredPosts, $this->okt->module('Pages')->filters->params->nb_per_page);

		$iNumPages = $oPager->getNbPages();

		$this->okt->module('Pages')->filters->normalizePage($iNumPages);

		$aParams['limit'] = (($this->okt->module('Pages')->filters->params->page - 1) * $this->okt->module('Pages')->filters->params->nb_per_page) . ',' . $this->okt->module('Pages')->filters->params->nb_per_page;

		# Récupération des pages
		$rsPages = $this->okt->module('Pages')->pages->getPages($aParams);

		# Liste des groupes si les permissions sont activées
		$aGroups = null;
		if ($this->okt->module('Pages')->config->enable_group_perms)
		{
			$aGroups = $this->okt->module('Pages')->pages->getUsersGroupsForPerms(true, true);
		}

		# Tableau de choix d'actions pour le traitement par lot
		$aActionsChoices = array(
			__('c_c_action_display') => 'show',
			__('c_c_action_hide') => 'hide'
		);

		if ($this->okt->checkPerm('pages_remove'))
		{
			$aActionsChoices[__('c_c_action_delete')] = 'delete';
		}

		return $this->render('Pages/Admin/Templates/Index', array(
			'sSearch' => $sSearch,
			'iNumFilteredPosts' => $iNumFilteredPosts,
			'oPager' => $oPager,
			'iNumPages' => $iNumPages,
			'rsPages' => $rsPages,
			'aGroups' => $aGroups,
			'aActionsChoices' => $aActionsChoices
		));
	}

	protected function getPagesJson()
	{
		$term = $this->request->query->get('term');

		if (! $this->request->isXmlHttpRequest() || ! $this->request->query->has('json') || empty($term))
		{
			return false;
		}

		$rsPages = $this->okt->module('Pages')->pages->getPagesRecordset(array(
			'language' => $this->okt->user->language,
			'search' => $term
		));

		$aResults = array();
		while ($rsPages->fetch())
		{
			$aResults[$rsPages->title] = $rsPages->title;
		}

		return $this->jsonResponse(array_unique($aResults));
	}

	protected function initFilters()
	{
		if ($this->request->query->has('init_filters'))
		{
			$this->okt->module('Pages')->filters->initFilters();

			return $this->redirect($this->generateUrl('Pages_index'));
		}

		return false;
	}

	protected function deletePost()
	{
		$iPostId = $this->request->query->getInt('delete');

		if (! $iPostId || ! $this->okt->checkPerm('pages_remove'))
		{
			return false;
		}

		if ($this->okt->module('Pages')->pages->deletePage($iPostId))
		{
			$this->okt->logAdmin->warning(array(
				'code' => 42,
				'component' => 'pages',
				'message' => 'page #' . $iPostId
			));

			$this->okt['flash']->success(__('m_pages_list_page_deleted'));

			return $this->redirect($this->generateUrl('Pages_index'));
		}

		return false;
	}

	protected function switchPostStatus()
	{
		$iPostId = $this->request->query->getInt('switch_status');

		if (! $iPostId)
		{
			return false;
		}

		if ($this->okt->module('Pages')->pages->switchPageStatus($iPostId))
		{
			$this->okt->logAdmin->info(array(
				'code' => 32,
				'component' => 'pages',
				'message' => 'post #' . $iPostId
			));

			return $this->redirect($this->generateUrl('Pages_index'));
		}

		return false;
	}

	protected function batches()
	{
		$sAction = $this->request->request->get('action');
		$aPagesId = $this->request->request->get('pages');

		if (! $sAction || ! $aPagesId || ! is_array($aPagesId))
		{
			return false;
		}

		$aPagesId = array_map('intval', $aPagesId);

		if ($sAction === 'show')
		{
			foreach ($aPagesId as $pageId)
			{
				if ($this->okt->module('Pages')->pages->setPageStatus($pageId, 1))
				{
					$this->okt->logAdmin->info(array(
						'code' => 30,
						'component' => 'pages',
						'message' => 'page #' . $pageId
					));
				}
			}
		}
		elseif ($sAction === 'hide')
		{
			foreach ($aPagesId as $pageId)
			{
				if ($this->okt->module('Pages')->pages->setPageStatus($pageId, 0))
				{
					$this->okt->logAdmin->info(array(
						'code' => 31,
						'component' => 'pages',
						'message' => 'page #' . $pageId
					));
				}
			}
		}
		elseif ($sAction === 'delete' && $this->okt->checkPerm('pages_remove'))
		{
			foreach ($aPagesId as $pageId)
			{
				if ($this->okt->module('Pages')->pages->deletePage($pageId))
				{
					$this->okt->logAdmin->warning(array(
						'code' => 42,
						'component' => 'pages',
						'message' => 'page #' . $pageId
					));
				}
			}
		}

		return $this->redirect($this->generateUrl('Pages_index'));
	}
}
