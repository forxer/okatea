<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Admin\Controller\Users;

use Okatea\Admin\Controller;
use Okatea\Admin\Pager;
use Okatea\Tao\Users\Authentification;

class Index extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('users')) {
			return $this->serve401();
		}

		# initialisation des filtres
		$this->okt->Users->filtersStart('admin');

		# Enable user status
		if ($iUserId = $this->request->query->getInt('enable'))
		{
			if ($this->okt->Users->setUserStatus($iUserId, 1))
			{
				# log admin
				$this->okt->logAdmin->info(array(
					'code' => 30,
					'component' => 'users',
					'message' => 'user #'.$iUserId
				));

				return $this->redirect($this->generateUrl('Users_index'));
			}
		}

		# Disable user status
		if ($iUserId = $this->request->query->getInt('disable'))
		{
			if ($this->okt->Users->setUserStatus($iUserId, 0))
			{
				# log admin
				$this->okt->logAdmin->info(array(
					'code' => 31,
					'component' => 'users',
					'message' => 'user #'.$iUserId
				));

				return $this->redirect($this->generateUrl('Users_index'));
			}
		}

		# Supprimer utilisateur
		if ($iUserId = $this->request->query->getInt('delete') && $this->okt->checkPerm('users_delete'))
		{
			if ($this->okt->Users->deleteUser($iUserId))
			{
				# log admin
				$this->okt->logAdmin->warning(array(
					'code' => 42,
					'component' => 'users',
					'message' => 'user #'.$iUserId
				));

				# -- CORE TRIGGER : adminModUsersDeleteProcess
				$this->okt->triggers->callTrigger('adminModUsersDeleteProcess', $iUserId);

				$this->okt->page->flash->success(__('m_users_user_deleted'));

				return $this->redirect($this->generateUrl('Users_index'));
			}
		}

		# Ré-initialisation filtres
		if ($this->request->query->has('init_filters'))
		{
			$this->okt->Users->filters->initFilters();
			return $this->redirect($this->generateUrl('Users_index'));
		}


		# initialisation des filtres
		$aParams = array();
		$aParams['group_id_not'][] = Authentification::guest_group_id;

		if (!$this->okt->user->is_superadmin) {
			$aParams['group_id_not'][] = Authentification::superadmin_group_id;
		}

		if (!$this->okt->user->is_admin) {
			$aParams['group_id_not'][] = Authentification::admin_group_id;
		}

		$sSearch = $this->request->query->get('search');

		if ($sSearch) {
			$aParams['search'] = $sSearch;
		}

		$this->okt->Users->filters->setUsersParams($aParams);

		# création des filtres
		$this->okt->Users->filters->getFilters();

		# initialisation de la pagination
		$iNumFilteredUsers = $this->okt->Users->getUsers($aParams,true);

		$pager = new Pager($this->okt, $this->okt->Users->filters->params->page, $iNumFilteredUsers, $this->okt->Users->filters->params->nb_per_page);

		$iNumPages = $pager->getNbPages();

		$this->okt->Users->filters->normalizePage($iNumPages);

		$aParams['limit'] = (($this->okt->Users->filters->params->page-1)*$this->okt->Users->filters->params->nb_per_page).','.$this->okt->Users->filters->params->nb_per_page;

		# liste des utilisateurs
		$rsUsers = $this->okt->Users->getUsers($aParams);

		# nombre d'utilisateur en attente de validation
		$iNumUsersWaitingValidation = $this->okt->Users->getUsers(array('group_id'=>Authentification::unverified_group_id), true);

		if ($iNumUsersWaitingValidation === 1) {
			$this->okt->page->warnings->set(__('m_users_one_user_in_wait_of_validation'));
		}
		elseif ($iNumUsersWaitingValidation > 1) {
			$this->okt->page->warnings->set(sprintf(__('m_users_%s_users_in_wait_of_validation'), $iNumUsersWaitingValidation));
		}

		return $this->render('Users/Admin/Templates/Index', array(
			'rsUsers' 						=> $rsUsers,
			'sSearch' 						=> $sSearch,
			'iNumFilteredUsers' 			=> $iNumFilteredUsers,
			'iNumUsersWaitingValidation' 	=> $iNumUsersWaitingValidation,
			'iNumPages' 					=> $iNumPages,
			'pager' 						=> $pager
		));
	}
}
