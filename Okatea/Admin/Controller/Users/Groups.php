<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Admin\Controller\Users;

use ArrayObject;
use Okatea\Admin\Controller;
use Okatea\Tao\Users\Groups as UsersGroups;

class Groups extends Controller
{

	public function index()
	{
		if (! $this->okt['visitor']->checkPerm('users_groups'))
		{
			return $this->serve401();
		}

		$this->okt['l10n']->loadFile($this->okt['locales_path'] . '/%s/admin/users');

		if ($this->okt['request']->query->has('delete_id'))
		{
			$iGroupIdToDelete = $this->okt['request']->query->get('delete_id');

			if ($this->okt['groups']->deleteGroup($iGroupIdToDelete))
			{
				$this->okt['flashMessages']->success(__('c_a_users_group_deleted'));

				return $this->redirect($this->generateUrl('Users_groups'));
			}
		}

		$aParams = array(
			'language' => $this->okt['visitor']->language
		);

		if (! $this->okt['visitor']->is_superadmin)
		{
			$aParams['group_id_not'][] = UsersGroups::SUPERADMIN;
		}

		if (! $this->okt['visitor']->is_admin)
		{
			$aParams['group_id_not'][] = UsersGroups::ADMIN;
		}

		$rsGroups = $this->okt['groups']->getGroups($aParams);

		return $this->render('Users/Groups/Index', array(
			'rsGroups' => $rsGroups
		));
	}

	public function add()
	{
		if (! $this->okt['visitor']->checkPerm('users_groups'))
		{
			return $this->serve401();
		}

		$this->okt['l10n']->loadFile($this->okt['locales_path'] . '/%s/admin/users');

		$aGroupData = new ArrayObject();

		$aGroupData['locales'] = array();

		foreach ($this->okt['languages']->list as $aLanguage)
		{
			$aGroupData['locales'][$aLanguage['code']] = array();
			$aGroupData['locales'][$aLanguage['code']]['title'] = '';
			$aGroupData['locales'][$aLanguage['code']]['description'] = '';
		}

		$aGroupData['perms'] = array();

		if ($this->okt['request']->request->has('form_sent'))
		{
			foreach ($this->okt['languages']->list as $aLanguage)
			{
				$aGroupData['locales'][$aLanguage['code']]['title'] = $this->okt['request']->request->get('p_title[' . $aLanguage['code'] . ']', '', true);
				$aGroupData['locales'][$aLanguage['code']]['description'] = $this->okt['request']->request->get('p_description[' . $aLanguage['code'] . ']', '', true);

				if (empty($aGroupData['locales'][$aLanguage['code']]['title']))
				{
					if ($this->okt['languages']->unique)
					{
						$this->okt['flashMessages']->error(__('c_a_users_must_enter_group_title'));
					}
					else
					{
						$this->okt['flashMessages']->error(sprintf(__('c_a_users_must_enter_group_title_in_%s'), $aLanguage['title']));
					}
				}
			}

			if ($this->okt['request']->request->has('perms'))
			{
				$aGroupData['perms'] = array_keys($this->okt['request']->request->get('perms'));
			}

			if (! $this->okt['flashMessages']->hasError())
			{
				if (($iGroupId = $this->okt['groups']->addGroup($aGroupData)) !== false)
				{
					$this->okt['flashMessages']->success(__('c_a_users_group_added'));

					return $this->redirect($this->generateUrl('Users_groups_edit', array(
						'group_id' => $iGroupId
					)));
				}
			}
		}

		return $this->render('Users/Groups/Add', array(
			'aGroupData' => $aGroupData,
			'aPermissions' => $this->okt['permissions']->getPermsForDisplay()
		));
	}

	public function edit()
	{
		if (! $this->okt['visitor']->checkPerm('users_groups'))
		{
			return $this->serve401();
		}

		$this->okt['l10n']->loadFile($this->okt['locales_path'] . '/%s/admin/users');

		$iGroupId = $this->okt['request']->attributes->getInt('group_id');

		if (empty($iGroupId))
		{
			return $this->serve404();
		}

		if (in_array($iGroupId, UsersGroups::$native))
		{
			$this->okt->page->warnings->set(__('c_a_users_edit_native_group'));
		}

		$rsGroup = $this->okt['groups']->getGroup($iGroupId);
		$rsGroupL10n = $this->okt['groups']->getGroupL10n($iGroupId);

		$aGroupData = new ArrayObject();

		$aGroupData['locales'] = array();

		while ($rsGroupL10n->fetch())
		{
			if (isset($this->okt['languages']->list[$rsGroupL10n->language]))
			{
				$aGroupData['locales'][$rsGroupL10n->language] = array();
				$aGroupData['locales'][$rsGroupL10n->language]['title'] = $rsGroupL10n->title;
				$aGroupData['locales'][$rsGroupL10n->language]['description'] = $rsGroupL10n->description;
			}
		}

		$aGroupData['perms'] = $rsGroup->perms ? json_decode($rsGroup->perms) : array();

		if ($this->okt['request']->request->has('form_sent'))
		{
			foreach ($this->okt['languages']->list as $aLanguage)
			{
				$aGroupData['locales'][$aLanguage['code']]['title'] = $this->okt['request']->request->get('p_title[' . $aLanguage['code'] . ']', '', true);
				$aGroupData['locales'][$aLanguage['code']]['description'] = $this->okt['request']->request->get('p_description[' . $aLanguage['code'] . ']', '', true);

				if (empty($aGroupData['locales'][$aLanguage['code']]['title']))
				{
					if ($this->okt['languages']->unique)
					{
						$this->okt['flashMessages']->error(__('c_a_users_must_enter_group_title'));
					}
					else
					{
						$this->okt['flashMessages']->error(sprintf(__('c_a_users_must_enter_group_title_in_%s'), $aLanguage['title']));
					}
				}
			}

			if ($this->okt['request']->request->has('perms'))
			{
				$aGroupData['perms'] = array_keys($this->okt['request']->request->get('perms'));
			}

			if (! $this->okt['flashMessages']->hasError())
			{
				if ($this->okt['groups']->updGroup($iGroupId, $aGroupData))
				{
					$this->okt['flashMessages']->success(__('c_a_users_group_edited'));

					return $this->redirect($this->generateUrl('Users_groups_edit', array(
						'group_id' => $iGroupId
					)));
				}
			}
		}

		return $this->render('Users/Groups/Edit', array(
			'iGroupId' => $iGroupId,
			'aGroupData' => $aGroupData,
			'aPermissions' => $this->okt['permissions']->getPermsForDisplay()
		));
	}
}
