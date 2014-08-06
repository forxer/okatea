<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Admin\Controller\Config;

use Okatea\Admin\Controller;
use Okatea\Tao\Users\Groups;

class Permissions extends Controller
{
	public function page()
	{
		if (!$this->okt['visitor']->checkPerm('permissions')) {
			return $this->serve401();
		}

		# locales
		$this->okt['l10n']->loadFile($this->okt['locales_path'] . '/%s/admin/permissions');

		$aGroups = array();
		$aPerms = array();

		$aParams = array(
			'language' => $this->okt['visitor']->language,
			'group_id_not' => array(
				Groups::SUPERADMIN,
				Groups::GUEST
			)
		);

		if (!$this->okt['visitor']->is_superadmin) {
			$aParams['group_id_not'][] = Groups::ADMIN;
		}

		foreach ($this->okt['groups']->getGroups($aParams) as $aGroup)
		{
			$aGroups[$aGroup['group_id']] = $aGroup['title'];

			$aPerms[$aGroup['group_id']] = $aGroup['perms'] ? json_decode($aGroup['perms']) : [];
		}

		if ($this->okt['request']->request->has('sended_form'))
		{
			$perms = $this->okt['request']->request->get('perms');

			foreach ($aGroups as $group_id => $group_title)
			{
				$group_perms = ! empty($perms[$group_id]) ? array_keys($perms[$group_id]) : array();

				$this->okt['groups']->updGroupPerms($group_id, $group_perms);
			}

			$this->okt['flashMessages']->success(__('c_a_config_permissions_updated'));

			return $this->redirect($this->generateUrl('config_permissions'));
		}

		return $this->render('Config/Permissions', array(
			'aGroups' => $aGroups,
			'aPerms' => $aPerms,
			'aPermissions' => $this->okt['permissions']->getPermsForDisplay()
		));
	}
}
