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
		if (!$this->okt->checkPerm('pages_usage')) {
			return $this->serve401();
		}

		if (($json = $this->getPagesJson()) !== false) {
			return $json;
		}

		# Chargement des locales
		$this->okt->l10n->loadFile(__DIR__.'/../../Locales/'.$this->okt->user->language.'/admin.list');

		# initialisation des filtres
		$this->okt->Pages->filtersStart('admin');

		# Ré-initialisation filtres
		if (($action = $this->initFilters()) !== false) {
			return $action;
		}

		# Delete page
		if (($action = $this->deletePost()) !== false) {
			return $action;
		}

		# Switch page statut
		if (($action = $this->switchPostStatus()) !== false) {
			return $action;
		}

		# Traitements par lots
		if (($action = $this->batches()) !== false) {
			return $action;
		}

		# Initialisation des filtres
		$aParams = array();

		$sSearch = $this->request->query->get('search');

		if ($sSearch) {
			$aParams['search'] = $sSearch;
		}

		$this->okt->Pages->filters->setPagesParams($aParams);

		# Création des filtres
		$this->okt->Pages->filters->getFilters();

		# Initialisation de la pagination
		$iNumFilteredPosts = $this->okt->Pages->getPagesCount($aParams);

		$oPager = new Pager($this->okt, $this->okt->Pages->filters->params->page, $iNumFilteredPosts, $this->okt->Pages->filters->params->nb_per_page);

		$iNumPages = $oPager->getNbPages();

		$this->okt->Pages->filters->normalizePage($iNumPages);

		$aParams['limit'] = (($this->okt->Pages->filters->params->page-1)*$this->okt->Pages->filters->params->nb_per_page).','.$this->okt->Pages->filters->params->nb_per_page;

		# Récupération des pages
		$rsPages = $this->okt->Pages->getPages($aParams);

		# Liste des groupes si les permissions sont activées
		$aGroups = null;
		if ($this->okt->Pages->canUsePerms()) {
			$aGroups = $this->okt->Pages->getUsersGroupsForPerms(true,true);
		}

		# Tableau de choix d'actions pour le traitement par lot
		$aActionsChoices = array(
			__('c_c_action_display') 	=> 'show',
			__('c_c_action_hide') 		=> 'hide'
		);

		if ($this->okt->checkPerm('pages_remove')) {
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
	    $json = $this->request->query->get('json');
	    $term = $this->request->query->get('term');
	
	    if (!$json || !$term || !$this->request->isXmlHttpRequest()) {
	        return false;
	    }
	
	    $rsPages = $this->okt->Pages->getPagesRecordset(array(
	        'language' => $this->okt->user->language,
	        'search' => $term
	    ));
	
	    $aResults = array();
	    while ($rsPages->fetch()) {
	        $aResults[$rsPages->title] = $rsPages->title;
	    }
	
	    return $this->jsonResponse(array_unique($aResults));
	}
	
	protected function initFilters()
	{
		$bInit = $this->request->query->has('init_filters');

		if (!$bInit) {
			return false;
		}

		$this->okt->Pages->filters->initFilters();

		return $this->redirect($this->generateUrl('Pages_index'));
	}

	protected function deletePost()
	{
		$iPostId = $this->request->query->getInt('delete');

		if (!$iPostId || !$this->okt->checkPerm('pages_remove')) {
			return false;
		}

		try
		{
			$this->okt->Pages->deletePage($iPostId);

			# log admin
			$this->okt->logAdmin->warning(array(
				'code' => 42,
				'component' => 'pages',
				'message' => 'page #'.$iPostId
			));

			$this->okt->page->flash->success(__('m_pages_list_page_deleted'));

			return $this->redirect($this->generateUrl('Pages_index'));
		}
		catch (Exception $e) {
			$this->okt->error->set($e->getMessage());
			return false;
		}
	}

	protected function switchPostStatus()
	{
		$iPostId = $this->request->query->getInt('switch_status');

		if (!$iPostId) {
			return false;
		}

		try
		{
			$this->okt->Pages->switchPageStatus($iPostId);

			# log admin
			$this->okt->logAdmin->info(array(
				'code' => 32,
				'component' => 'pages',
				'message' => 'post #'.$iPostId
			));

			return $this->redirect($this->generateUrl('Pages_index'));
		}
		catch (Exception $e) {
			$this->okt->error->set($e->getMessage());
			return false;
		}
	}

	protected function batches()
	{
		$sAction = $this->request->request->get('actions');
		$aPagesId = $this->request->request->get('pages');

		if (!$sAction || !$aPagesId || !is_array($aPagesId)) {
			return false;
		}

		$aPagesId = array_map('intval', $aPagesId);

		try
		{
			if ($sAction == 'show')
			{
				foreach ($aPagesId as $pageId)
				{
					$this->okt->Pages->setPageStatus($pageId,1);

					# log admin
					$this->okt->logAdmin->info(array(
					'code' => 30,
					'component' => 'pages',
					'message' => 'page #'.$pageId
					));
				}

				return $this->redirect($this->generateUrl('Pages_index'));
			}
			elseif ($sAction == 'hide')
			{
				foreach ($aPagesId as $pageId)
				{
					$this->okt->Pages->setPageStatus($pageId,0);

					# log admin
					$this->okt->logAdmin->info(array(
						'code' => 31,
						'component' => 'pages',
						'message' => 'page #'.$pageId
					));
				}

				return $this->redirect($this->generateUrl('Pages_index'));
			}
			elseif ($sAction == 'delete' && $this->okt->checkPerm('pages_remove'))
			{
				foreach ($aPagesId as $pageId)
				{
					$this->okt->Pages->deletePage($pageId);

					# log admin
					$this->okt->logAdmin->warning(array(
						'code' => 42,
						'component' => 'pages',
						'message' => 'page #'.$pageId
					));
				}

				return $this->redirect($this->generateUrl('Pages_index'));
			}
		}
		catch (Exception $e) {
			$this->okt->error->set($e->getMessage());
			return false;
		}
	}
}
