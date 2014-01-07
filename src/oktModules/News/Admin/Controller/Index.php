<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Module\News\Admin\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Tao\Admin\Controller;
use Tao\Admin\Pager;

class Index extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('news_usage') && !$this->okt->checkPerm('news_contentadmin')) {
			return $this->serve401();
		}

		if ($json = $this->getPostsJson() !== false) {
			return $json;
		}

		# Chargement des locales
		$this->okt->l10n->loadFile(__DIR__.'/../../Locales/'.$this->okt->user->language.'/admin.list');

		# initialisation des filtres
		$this->okt->News->filtersStart('admin');

		# Ré-initialisation filtres
		if ($initFilters = $this->initFilters() !== false) {
			return $initFilters;
		}

		# Suppression d'un article
		if ($deletePost = $this->deletePost() !== false) {
			return $deletePost;
		}

		# Switch post statut
		if ($switchPostStatus = $this->switchPostStatus() !== false) {
			return $switchPostStatus;
		}

		# Switch article selection
		if ($switchPostSelect = $this->switchPostSelect() !== false) {
			return $switchPostSelect;
		}

		# Sélectionne un article
		if ($selectPost = $this->selectPost() !== false) {
			return $selectPost;
		}

		# Déselectionne un article
		if ($unselectPost = $this->unselectPost() !== false) {
			return $unselectPost;
		}

		# Publication d'un article
		if ($publishPost = $this->publishPost() !== false) {
			return $publishPost;
		}

		# Traitements par lots
		if ($batches = $this->batches() !== false) {
			return $batches;
		}

		# Publication des articles différés
		$this->okt->News->publishScheduledPosts();

		# Initialisation des filtres
		$aParams = array();

		if (!$this->okt->checkPerm('news_contentadmin') && !$this->okt->checkPerm('news_show_all')) {
			$aParams['user_id'] = $this->okt->user->id;
		}

		$sSearch = $this->request->query->get('search');

		if ($sSearch) {
			$aParams['search'] = $sSearch;
		}

		$this->okt->News->filters->setPostsParams($aParams);

		# Création des filtres
		$this->okt->News->filters->getFilters();

		# Initialisation de la pagination
		$iNumFilteredPosts = $this->okt->News->getPostsCount($aParams);

		$oPager = new Pager($this->okt, $this->okt->News->filters->params->page, $iNumFilteredPosts, $this->okt->News->filters->params->nb_per_page);

		$iNumPages = $oPager->getNbPages();

		$this->okt->News->filters->normalizePage($iNumPages);

		$aParams['limit'] = (($this->okt->News->filters->params->page-1)*$this->okt->News->filters->params->nb_per_page).','.$this->okt->News->filters->params->nb_per_page;

		# Récupération des articles
		$rsPosts = $this->okt->News->getPosts($aParams);

		# Liste des groupes si les permissions sont activées
		$aGroups = null;
		if ($this->okt->News->canUsePerms()) {
			$aGroups = $this->okt->News->getUsersGroupsForPerms(true,true);
		}

		# Tableau de choix d'actions pour le traitement par lot
		$aActionsChoices = array(
			'&nbsp;' => null,
			__('m_news_list_status') => array(
				__('c_c_action_display') 	=> 'show',
				__('c_c_action_hide') 		=> 'hide'
			)
		);

		if ($this->okt->checkPerm('news_publish') || $this->okt->checkPerm('news_contentadmin'))  {
			$aActionsChoices[__('m_news_list_status')][__('c_c_action_publish')] = 'publish';
		}

		$aActionsChoices[__('m_news_list_mark')] = array(
			__('c_c_action_select') 	=> 'selected',
			__('c_c_action_deselect') 	=> 'unselected'
		);

		if ($this->okt->checkPerm('news_delete') || $this->okt->checkPerm('news_contentadmin'))  {
			$aActionsChoices[__('c_c_action_Delete')][__('c_c_action_delete')] = 'delete';
		}

		return $this->render('News/Admin/Templates/Index', array(
			'rsPosts' 			=> $rsPosts,
			'aGroups' 			=> $aGroups,
			'aActionsChoices' 	=> $aActionsChoices,
			'iNumFilteredPosts' => $iNumFilteredPosts,
			'iNumPages' 		=> $iNumPages,
			'oPager' 			=> $oPager,
			'sSearch' 			=> $sSearch
		));
	}

	protected function getPostsJson()
	{
		$json = $this->request->query->get('json');
		$term = $this->request->query->get('term');

		if (!$json) {
			return false;
		}

		$rsPosts = $this->okt->News->getPostsRecordset(array(
			'language' => $this->okt->user->language,
			'search' => $term
		));

		$aResults = array();
		while ($rsPosts->fetch()) {
			$aResults[$rsPosts->title] = $rsPosts->title;
		}

		$this->response = new JsonResponse();
		$this->response->setData($aResults);

		return $this->response;
	}

	protected function initFilters()
	{
		$bInit = $this->request->query->has('init_filters');

		if (!$bInit) {
			return false;
		}

		$this->okt->News->filters->initFilters();

		return $this->redirect($this->generateUrl('News_index'));
	}

	protected function deletePost()
	{
		$iPostId = $this->request->query->getInt('delete');

		if (!$iPostId || !$this->okt->checkPerm('news_delete')) {
			return false;
		}

		try
		{
			$this->okt->News->deletePost($iPostId);

			# log admin
			$this->okt->logAdmin->warning(array(
				'code' => 42,
				'component' => 'news',
				'message' => 'post #'.$iPostId
			));

			$this->okt->page->flash->success(__('m_news_list_post_deleted'));

			return $this->redirect($this->generateUrl('News_index'));
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
			$this->okt->News->switchPostStatus($iPostId);

			# log admin
			$this->okt->logAdmin->info(array(
				'code' => 32,
				'component' => 'news',
				'message' => 'post #'.$iPostId
			));

			return $this->redirect($this->generateUrl('News_index'));
		}
		catch (Exception $e) {
			$this->okt->error->set($e->getMessage());
			return false;
		}
	}

	protected function switchPostSelect()
	{
		$iPostId = $this->request->query->getInt('switch_selected');

		if (!$iPostId) {
			return false;
		}

		try
		{
			$this->okt->News->switchPostSelected($iPostId);

			# log admin
			$this->okt->logAdmin->info(array(
				'code' => 41,
				'component' => 'news',
				'message' => 'post #'.$iPostId
			));

			return $this->redirect($this->generateUrl('News_index'));
		}
		catch (Exception $e) {
			$this->okt->error->set($e->getMessage());
			return false;
		}
	}

	protected function selectPost()
	{
		$iPostId = $this->request->query->getInt('select');

		if (!$iPostId) {
			return false;
		}

		try
		{
			$this->okt->News->setPostSelected($iPostId, true);

			# log admin
			$this->okt->logAdmin->info(array(
				'code' => 41,
				'component' => 'news',
				'message' => 'post #'.$iPostId
			));

			$this->okt->page->flash->success(__('m_news_list_post_selected'));

			return $this->redirect($this->generateUrl('News_index'));
		}
		catch (Exception $e) {
			$this->okt->error->set($e->getMessage());
			return false;
		}
	}

	protected function unselectPost()
	{
		$iPostId = $this->request->query->getInt('deselect');

		if (!$iPostId) {
			return false;
		}

		try
		{
			$this->okt->News->setPostSelected($iPostId, false);

			# log admin
			$this->okt->logAdmin->info(array(
				'code' => 41,
				'component' => 'news',
				'message' => 'post #'.$iPostId
			));

			$this->okt->page->flash->success(__('m_news_list_post_deselected'));

			return $this->redirect($this->generateUrl('News_index'));
		}
		catch (Exception $e) {
			$this->okt->error->set($e->getMessage());
			return false;
		}
	}

	protected function publishPost()
	{
		$iPostId = $this->request->query->getInt('publish');

		if (!$iPostId) {
			return false;
		}

		try
		{
			$this->okt->News->publishPost($iPostId);

			# log admin
			$this->okt->logAdmin->info(array(
				'code' => 30,
				'component' => 'news',
				'message' => 'post #'.$iPostId
			));

			$this->okt->page->flash->success(__('m_news_list_post_published'));

			return $this->redirect($this->generateUrl('News_index'));
		}
		catch (Exception $e) {
			$this->okt->error->set($e->getMessage());
			return false;
		}
	}

	protected function batches()
	{
		$sAction = $this->request->request->get('actions');
		$aPostsId = $this->request->request->get('posts');

		if (!$sAction || !$aPostsId || !is_array($aPostsId)) {
			return false;
		}

		$aPostsId = array_map('intval', $aPostsId);

		try
		{
			if ($sAction === 'show')
			{
				foreach ($aPostsId as $iPostId)
				{
					$this->okt->News->showPost($iPostId,1);

					# log admin
					$this->okt->logAdmin->info(array(
						'code' => 30,
						'component' => 'news',
						'message' => 'post #'.$iPostId
					));
				}

				return $this->redirect($this->generateUrl('News_index'));
			}
			elseif ($sAction === 'hide')
			{
				foreach ($aPostsId as $iPostId)
				{
					$this->okt->News->hidePost($iPostId);

					# log admin
					$this->okt->logAdmin->info(array(
						'code' => 31,
						'component' => 'news',
						'message' => 'post #'.$iPostId
					));
				}

				return $this->redirect($this->generateUrl('News_index'));
			}
			elseif ($sAction === 'publish')
			{
				foreach ($aPostsId as $iPostId)
				{
					$this->okt->News->publishPost($iPostId);

					# log admin
					$this->okt->logAdmin->info(array(
						'code' => 30,
						'component' => 'news',
						'message' => 'post #'.$iPostId
					));
				}

				$this->okt->page->flash->success(__('m_news_list_posts_published'));

				return $this->redirect($this->generateUrl('News_index'));
			}
			elseif ($sAction === 'selected')
			{
				foreach ($aPostsId as $iPostId)
				{
					$this->okt->News->setPostSelected($iPostId,1);

					# log admin
					$this->okt->logAdmin->info(array(
						'code' => 41,
						'component' => 'news',
						'message' => 'post #'.$iPostId
					));
				}

				$this->okt->page->flash->success(__('m_news_list_posts_selected'));

				return $this->redirect($this->generateUrl('News_index'));
			}
			elseif ($sAction === 'unselected')
			{
				foreach ($aPostsId as $iPostId)
				{
					$this->okt->News->setPostSelected($iPostId, 0);

					# log admin
					$this->okt->logAdmin->info(array(
						'code' => 41,
						'component' => 'news',
						'message' => 'post #'.$iPostId
					));
				}

				$this->okt->page->flash->success(__('m_news_list_posts_deselected'));

				return $this->redirect($this->generateUrl('News_index'));
			}
			elseif ($sAction === 'delete' && $this->okt->checkPerm('news_delete'))
			{
				foreach ($aPostsId as $iPostId)
				{
					$this->okt->News->deletePost($iPostId);

					# log admin
					$this->okt->logAdmin->warning(array(
						'code' => 42,
						'component' => 'news',
						'message' => 'post #'.$iPostId
					));
				}

				$this->okt->page->flash->success(__('m_news_list_posts_deleted'));

				return $this->redirect($this->generateUrl('News_index'));
			}
		}
		catch (Exception $e) {
			$this->okt->error->set($e->getMessage());
			return false;
		}
	}
}
