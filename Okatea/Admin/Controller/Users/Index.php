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
use Okatea\Admin\Filters\Users as UsersFilters;
use Okatea\Tao\Users\Users;
use Okatea\Tao\Users\Groups;

class Index extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('users')) {
			return $this->serve401();
		}

		$this->okt->l10n->loadFile($this->okt->options->get('locales_dir').'/'.$this->okt->user->language.'/admin/users');

		$oUsers = new Users($this->okt);

		# json users list for autocomplete
		if ($this->request->query->has('json') && $this->request->query->has('term') && $this->request->isXmlHttpRequest())
		{
			$aParams = array();
			$aParams['group_id_not'][] = Groups::GUEST;

			if (!$this->okt->user->is_superadmin) {
				$aParams['group_id_not'][] = Groups::SUPERADMIN;
			}

			if (!$this->okt->user->is_admin) {
				$aParams['group_id_not'][] = Groups::ADMIN;
			}

			$aParams['search'] = $this->request->query->get('term');

			$rsUsers = $oUsers->getUsers($aParams);

			$aResults = array();
			while ($rsUsers->fetch())
			{
				$aResults[] = $rsUsers->username;
				$aResults[] = $rsUsers->email;
				if (!empty($rsUsers->firstname)) {
					$aResults[] = $rsUsers->firstname;
				}
				if (!empty($rsUsers->lastname)) {
					$aResults[] = $rsUsers->lastname;
				}
			}

			return $this->jsonResponse(array_unique($aResults));
		}

		# initialisation des filtres
		$oFilters = new UsersFilters($this->okt, 'admin');

		# Enable user status
		if ($iEnableUserId = $this->request->query->getInt('enable'))
		{
			if ($oUsers->setUserStatus($iEnableUserId, 1))
			{
				# log admin
				$this->okt->logAdmin->info(array(
					'code' => 30,
					'component' => 'users',
					'message' => 'user #'.$iEnableUserId
				));

				return $this->redirect($this->generateUrl('Users_index'));
			}
		}

		# Disable user status
		if ($iDisableUserId = $this->request->query->getInt('disable'))
		{
			if ($oUsers->setUserStatus($iDisableUserId, 0))
			{
				# log admin
				$this->okt->logAdmin->info(array(
					'code' => 31,
					'component' => 'users',
					'message' => 'user #'.$iDisableUserId
				));

				return $this->redirect($this->generateUrl('Users_index'));
			}
		}

		# Supprimer utilisateur
		if (($iDeleteUserId = $this->request->query->getInt('delete')) && $this->okt->checkPerm('users_delete'))
		{
			if ($oUsers->deleteUser($iDeleteUserId))
			{
				# log admin
				$this->okt->logAdmin->warning(array(
					'code' => 42,
					'component' => 'users',
					'message' => 'user #'.$iDeleteUserId
				));

				# -- CORE TRIGGER : adminModUsersDeleteProcess
				$this->okt->triggers->callTrigger('adminModUsersDeleteProcess', $iDeleteUserId);

				$this->okt->page->flash->success(__('c_a_users_user_deleted'));

				return $this->redirect($this->generateUrl('Users_index'));
			}
		}

		# Ré-initialisation filtres
		if ($this->request->query->has('init_filters'))
		{
			$oFilters->initFilters();
			return $this->redirect($this->generateUrl('Users_index'));
		}


		# initialisation des filtres
		$aParams = array();
		$aParams['group_id_not'][] = Groups::GUEST;

		if (!$this->okt->user->is_superadmin) {
			$aParams['group_id_not'][] = Groups::SUPERADMIN;
		}

		if (!$this->okt->user->is_admin) {
			$aParams['group_id_not'][] = Groups::ADMIN;
		}

		$sSearch = $this->request->query->get('search');

		if ($sSearch) {
			$aParams['search'] = $sSearch;
		}

		$oFilters->setUsersParams($aParams);

		# création des filtres
		$oFilters->getFilters();

		# initialisation de la pagination
		$iNumFilteredUsers = $oUsers->getUsers($aParams,true);

		$pager = new Pager($this->okt, $oFilters->params->page, $iNumFilteredUsers, $oFilters->params->nb_per_page);

		$iNumPages = $pager->getNbPages();

		$oFilters->normalizePage($iNumPages);

		$aParams['limit'] = (($oFilters->params->page-1)*$oFilters->params->nb_per_page).','.$oFilters->params->nb_per_page;

		# liste des utilisateurs
		$rsUsers = $oUsers->getUsers($aParams);

		# nombre d'utilisateur en attente de validation
		$iNumUsersWaitingValidation = $oUsers->getUsers(array('group_id'=>Groups::UNVERIFIED), true);

		if ($iNumUsersWaitingValidation === 1) {
			$this->okt->page->warnings->set(__('c_a_users_one_user_in_wait_of_validation'));
		}
		elseif ($iNumUsersWaitingValidation > 1) {
			$this->okt->page->warnings->set(sprintf(__('c_a_users_%s_users_in_wait_of_validation'), $iNumUsersWaitingValidation));
		}

		return $this->render('Users/Index', array(
			'users'                         => $oUsers,
			'filters'                       => $oFilters,
			'rsUsers' 						=> $rsUsers,
			'sSearch' 						=> $sSearch,
			'iNumFilteredUsers' 			=> $iNumFilteredUsers,
			'iNumUsersWaitingValidation' 	=> $iNumUsersWaitingValidation,
			'iNumPages' 					=> $iNumPages,
			'pager' 						=> $pager
		));
	}
}
