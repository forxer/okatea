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
use Okatea\Tao\Users\Groups;
use Okatea\Tao\Html\Escaper;

class Index extends Controller
{
	public function page()
	{
		if (! $this->okt['visitor']->checkPerm('users')) {
			return $this->serve401();
		}
		
		$this->okt['l10n']->loadFile($this->okt['locales_path'] . '/%s/admin/users');
		
		# json users list for autocomplete
		if (($json = $this->getUsersJson()) !== false)
		{
			return $json;
		}
		
		# Enable user status
		if (($action = $this->enableUser()) !== false)
		{
			return $action;
		}
		
		# Disable user status
		if (($action = $this->disableUser()) !== false)
		{
			return $action;
		}
		
		# Delete user
		if (($action = $this->deleteUser()) !== false)
		{
			return $action;
		}
		
		# Traitements par lots
		if (($action = $this->batches()) !== false)
		{
			return $action;
		}
		
		# initialisation des filtres
		$oFilters = new UsersFilters($this->okt, 'admin');
		
		# Ré-initialisation filtres
		if ($this->okt['request']->query->has('init_filters'))
		{
			$oFilters->initFilters();
			return $this->redirect($this->generateUrl('Users_index'));
		}
		
		$aParams = array();
		
		$aParams['group_id_not'][] = Groups::GUEST;
		
		if (! $this->okt['visitor']->is_superadmin)
		{
			$aParams['group_id_not'][] = Groups::SUPERADMIN;
		}
		
		if (! $this->okt['visitor']->is_admin)
		{
			$aParams['group_id_not'][] = Groups::ADMIN;
		}
		
		$sSearch = $this->okt['request']->query->get('search');
		
		if ($sSearch)
		{
			$aParams['search'] = $sSearch;
		}
		
		$oFilters->setUsersParams($aParams);
		
		# création des filtres
		$oFilters->getFilters();
		
		# initialisation de la pagination
		$iNumFilteredUsers = $this->okt['users']->getUsers($aParams, true);
		
		$pager = new Pager($this->okt, $oFilters->params->page, $iNumFilteredUsers, $oFilters->params->nb_per_page);
		
		$iNumPages = $pager->getNbPages();
		
		$oFilters->normalizePage($iNumPages);
		
		$aParams['limit'] = (($oFilters->params->page - 1) * $oFilters->params->nb_per_page) . ',' . $oFilters->params->nb_per_page;
		
		# liste des utilisateurs
		$rsUsers = $this->okt['users']->getUsers($aParams);
		
		# liste des groupes
		$rsGroups = $this->okt['groups']->getGroups(array(
			'language' => $this->okt['visitor']->language
		));
		$aGroups = array();
		while ($rsGroups->fetch())
		{
			$aGroups[$rsGroups->group_id] = Escaper::html($rsGroups->title);
		}
		
		# Tableau de choix d'actions pour le traitement par lot
		$aActionsChoices = array(
			__('c_c_action_enable') => 'enable',
			__('c_c_action_disable') => 'disable'
		);
		
		if ($this->okt['visitor']->checkPerm('users_delete'))
		{
			$aActionsChoices[__('c_c_action_delete')] = 'delete';
		}
		
		# nombre d'utilisateur en attente de validation
		$iNumUsersWaitingValidation = $this->okt['users']->getUsers(array(
			'group_id' => Groups::UNVERIFIED
		), true);
		
		if ($iNumUsersWaitingValidation === 1)
		{
			$this->okt->page->warnings->set(__('c_a_users_one_user_in_wait_of_validation'));
		}
		elseif ($iNumUsersWaitingValidation > 1)
		{
			$this->okt->page->warnings->set(sprintf(__('c_a_users_%s_users_in_wait_of_validation'), $iNumUsersWaitingValidation));
		}
		
		return $this->render('Users/Index', array(
			'filters' => $oFilters,
			'rsUsers' => $rsUsers,
			'aGroups' => $aGroups,
			'sSearch' => $sSearch,
			'iNumFilteredUsers' => $iNumFilteredUsers,
			'iNumUsersWaitingValidation' => $iNumUsersWaitingValidation,
			'iNumPages' => $iNumPages,
			'pager' => $pager,
			'aActionsChoices' => $aActionsChoices
		));
	}

	protected function getUsersJson()
	{
		$term = $this->okt['request']->query->get('term');
		
		if (! $this->okt['request']->isXmlHttpRequest() || ! $this->okt['request']->query->has('json') || empty($term))
		{
			return false;
		}
		
		$aParams = array(
			'search' => $term
		);
		
		$aParams['group_id_not'][] = Groups::GUEST;
		
		if (! $this->okt['visitor']->is_superadmin)
		{
			$aParams['group_id_not'][] = Groups::SUPERADMIN;
		}
		
		if (! $this->okt['visitor']->is_admin)
		{
			$aParams['group_id_not'][] = Groups::ADMIN;
		}
		
		$rsUsers = $this->okt['users']->getUsers($aParams);
		
		$aResults = array();
		while ($rsUsers->fetch())
		{
			$aResults[] = $rsUsers->username;
			$aResults[] = $rsUsers->email;
			if (! empty($rsUsers->firstname))
			{
				$aResults[] = $rsUsers->firstname;
			}
			if (! empty($rsUsers->lastname))
			{
				$aResults[] = $rsUsers->lastname;
			}
		}
		
		return $this->jsonResponse(array_unique($aResults));
	}

	protected function enableUser()
	{
		$iUserId = $this->okt['request']->query->getInt('enable');
		
		if (empty($iUserId))
		{
			return false;
		}
		
		try
		{
			$this->okt['users']->setUserStatus($iUserId, 1);
			
			# log admin
			$this->okt['logAdmin']->info(array(
				'code' => 30,
				'component' => 'users',
				'message' => 'user #' . $iUserId
			));
			
			return $this->redirect($this->generateUrl('Users_index'));
		}
		catch (\Exception $e)
		{
			$this->okt['flash']->error($e->getMessage());
			return false;
		}
	}

	protected function disableUser()
	{
		$iUserId = $this->okt['request']->query->getInt('disable');
		
		if (empty($iUserId))
		{
			return false;
		}
		
		try
		{
			$this->okt['users']->setUserStatus($iUserId, 0);
			
			# log admin
			$this->okt['logAdmin']->info(array(
				'code' => 31,
				'component' => 'users',
				'message' => 'user #' . $iUserId
			));
			
			return $this->redirect($this->generateUrl('Users_index'));
		}
		catch (\Exception $e)
		{
			$this->okt['flash']->error($e->getMessage());
			return false;
		}
	}

	protected function deleteUser()
	{
		$iUserId = $this->okt['request']->query->getInt('delete');
		
		if (! $iUserId || ! $this->okt['visitor']->checkPerm('users_delete'))
		{
			return false;
		}
		
		try
		{
			# -- CORE TRIGGER : adminUsersBeforeDeleteProcess
			$this->okt['triggers']->callTrigger('adminUsersBeforeDeleteProcess', $iUserId);
			
			$this->okt['users']->deleteUser($iUserId);
			
			# log admin
			$this->okt['logAdmin']->warning(array(
				'code' => 42,
				'component' => 'users',
				'message' => 'user #' . $iUserId
			));
			
			# -- CORE TRIGGER : adminUsersAfterDeleteProcess
			$this->okt['triggers']->callTrigger('adminUsersAfterDeleteProcess', $iUserId);
			
			$this->okt['flash']->success(__('c_a_users_user_deleted'));
			
			return $this->redirect($this->generateUrl('Users_index'));
		}
		catch (\Exception $e)
		{
			$this->okt['flash']->error($e->getMessage());
			return false;
		}
	}

	protected function batches()
	{
		$sAction = $this->okt['request']->request->get('action');
		$aUsersIds = $this->okt['request']->request->get('users');
		
		if (! $sAction || empty($aUsersIds) || ! is_array($aUsersIds))
		{
			return false;
		}
		
		$aUsersIds = array_map('intval', $aUsersIds);
		
		try
		{
			if ($sAction == 'enable')
			{
				foreach ($aUsersIds as $iUserId)
				{
					$this->okt['users']->setUserStatus($iUserId, 1);
					
					# log admin
					$this->okt['logAdmin']->info(array(
						'code' => 30,
						'component' => 'users',
						'message' => 'user #' . $iUserId
					));
				}
				
				return $this->redirect($this->generateUrl('Users_index'));
			}
			elseif ($sAction == 'disable')
			{
				foreach ($aUsersIds as $iUserId)
				{
					$this->okt['users']->setUserStatus($iUserId, 0);
					
					# log admin
					$this->okt['logAdmin']->info(array(
						'code' => 31,
						'component' => 'users',
						'message' => 'user #' . $iUserId
					));
				}
				
				return $this->redirect($this->generateUrl('Users_index'));
			}
			elseif ($sAction == 'delete' && $this->okt['visitor']->checkPerm('users_delete'))
			{
				foreach ($aUsersIds as $iUserId)
				{
					$this->okt['users']->deleteUser($iUserId);
					
					# log admin
					$this->okt['logAdmin']->warning(array(
						'code' => 42,
						'component' => 'users',
						'message' => 'user #' . $iUserId
					));
				}
				
				return $this->redirect($this->generateUrl('Users_index'));
			}
		}
		catch (\Exception $e)
		{
			$this->okt['flash']->error($e->getMessage());
			return false;
		}
	}
}
