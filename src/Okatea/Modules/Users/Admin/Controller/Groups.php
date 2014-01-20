<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Users\Admin\Controller;

use Okatea\Admin\Controller;
use Okatea\Tao\Authentification;

class Groups extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('users_groups')) {
			return $this->serve401();
		}

		$iGroupId = $this->okt->request->query->getInt('group_id');

		$add_title = '';

		$edit_title = '';

		if ($iGroupId) {
			$group = $this->okt->Users->getGroup($iGroupId);
			$edit_title = $group->title;
		}

		# ajout d'un groupe
		if ($this->okt->request->request->has('add_title'))
		{
			$add_title = $this->okt->request->request->get('add_title');

			if (empty($add_title)) {
				$this->okt->error->set(__('m_users_must_enter_group_title'));
			}

			if ($this->okt->error->isEmpty())
			{
				$this->okt->Users->addGroup($add_title);

				$this->okt->page->flash->success(__('m_users_group_added'));

				return $this->redirect($this->generateUrl('Users_groups'));
			}
		}

		# modification d'un groupe
		if ($this->okt->request->request->has('edit_title'))
		{
			$edit_title = $this->okt->request->request->get('edit_title');

			if (empty($edit_title)) {
				$this->okt->error->set(__('m_users_must_enter_group_title'));
			}

			if ($this->okt->error->isEmpty())
			{
				$this->okt->Users->updGroup($iGroupId, $edit_title);

				$this->okt->page->flash->success(__('m_users_group_edited'));

				return $this->redirect($this->generateUrl('Users_groups'));
			}
		}

		# suppression d'un groupe
		if ($this->okt->request->query->has('delete_id'))
		{
			$iGroupIdToDelete = $this->okt->request->query->get('delete_id');

			if (in_array($iGroupIdToDelete, array(Authentification::superadmin_group_id, Authentification::admin_group_id, Authentification::guest_group_id, Authentification::member_group_id))) {
				$this->okt->error->set(__('m_users_cannot_remove_group'));
			}
			else
			{
				$this->okt->Users->deleteGroup($iGroupIdToDelete);

				$this->okt->page->flash->success(__('m_users_group_deleted'));

				return $this->redirect($this->generateUrl('Users_groups'));
			}
		}

		# Liste des groupes
		$rsGroups = $this->okt->Users->getGroups();

		return $this->render('Users/Admin/Templates/Groups', array(
			'iGroupId' => $iGroupId,
			'rsGroups' => $rsGroups,
			'add_title' => $add_title,
			'edit_title' => $edit_title
		));
	}
}
