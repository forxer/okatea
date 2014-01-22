<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Admin\Controller\Users;

use Okatea\Admin\Controller;
use Okatea\Tao\Users\Authentification;
use Okatea\Tao\Users\Groups as UsersGroups;
use Okatea\Tao\Users\Users;

class Groups extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('users_groups')) {
			return $this->serve401();
		}
		
		$oUsers = new Users($this->okt);
		$oUsersGroups = new UsersGroups($this->okt);

		$iGroupId = $this->okt->request->query->getInt('group_id');

		$add_title = '';

		$edit_title = '';

		if ($iGroupId) 
		{
			$group = $oUsersGroups->getGroup($iGroupId);
			$edit_title = $group->title;
		}

		# ajout d'un groupe
		if ($this->okt->request->request->has('add_title'))
		{
			$add_title = $this->okt->request->request->get('add_title');

			if (empty($add_title)) {
				$this->okt->error->set(__('c_a_users_must_enter_group_title'));
			}

			if ($this->okt->error->isEmpty())
			{
				$oUsersGroups->addGroup($add_title);

				$this->okt->page->flash->success(__('c_a_users_group_added'));

				return $this->redirect($this->generateUrl('Users_groups'));
			}
		}

		# modification d'un groupe
		if ($this->okt->request->request->has('edit_title'))
		{
			$edit_title = $this->okt->request->request->get('edit_title');

			if (empty($edit_title)) {
				$this->okt->error->set(__('c_a_users_must_enter_group_title'));
			}

			if ($this->okt->error->isEmpty())
			{
				$oUsersGroups->updGroup($iGroupId, $edit_title);

				$this->okt->page->flash->success(__('c_a_users_group_edited'));

				return $this->redirect($this->generateUrl('Users_groups'));
			}
		}

		# suppression d'un groupe
		if ($this->okt->request->query->has('delete_id'))
		{
			$iGroupIdToDelete = $this->okt->request->query->get('delete_id');

			if (in_array($iGroupIdToDelete, array(UsersGroups::SUPERADMIN, UsersGroups::ADMIN, UsersGroups::GUEST, UsersGroups::MEMBER))) {
				$this->okt->error->set(__('c_a_users_cannot_remove_group'));
			}
			else
			{
				$this->okt->Users->deleteGroup($iGroupIdToDelete);

				$this->okt->page->flash->success(__('c_a_users_group_deleted'));

				return $this->redirect($this->generateUrl('Users_groups'));
			}
		}

		# Liste des groupes
		$rsGroups = $oUsersGroups->getGroups();

		return $this->render('Users/Groups', array(
		    'users'          => $oUsers,
		    'groups'         => $oUsersGroups,
			'iGroupId'       => $iGroupId,
			'rsGroups'       => $rsGroups,
			'add_title'      => $add_title,
			'edit_title'     => $edit_title
		));
	}
}
