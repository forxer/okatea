<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Admin\Controller\Config;

use Okatea\Admin\Controller;
use Okatea\Tao\Core\Authentification;

class Permissions extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('permissions')) {
			return $this->serve401();
		}

		# locales
		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/'.$this->okt->user->language.'/admin.permissions');

		$aGroups = array();
		$aPerms = array();
		$sTgroups = $this->okt->db->prefix.'core_users_groups';

		$rsGroups = $this->okt->db->select('SELECT group_id, title, perms FROM '.$sTgroups);

		while ($rsGroups->fetch())
		{
			if ($rsGroups->group_id == Authentification::superadmin_group_id || $rsGroups->group_id == Authentification::guest_group_id) {
				continue;
			}
			elseif (!$this->okt->user->is_superadmin && $rsGroups->group_id == Authentification::admin_group_id) {
				continue;
			}

			$aGroups[$rsGroups->group_id] = $rsGroups->title;
			$aPerms[$rsGroups->group_id] = $rsGroups->perms ? unserialize($rsGroups->perms) : array();
		}
		unset($rsGroups);

		if ($this->request->request->has('sended_form'))
		{
			$perms = $this->request->request->get('perms');

			foreach ($aGroups as $group_id=>$group_title)
			{
				$group_perms = !empty($perms[$group_id]) ? array_keys($perms[$group_id]) : array();
				$group_perms = serialize($group_perms);

				$query =
				'UPDATE '.$sTgroups.' SET '.
				'perms=\''.$this->okt->db->escapeStr($group_perms).'\' '.
				'WHERE group_id='.(integer)$group_id;

				$this->okt->db->execute($query);
			}

			$this->page->flash->success(__('c_a_config_permissions_updated'));

			return $this->redirect($this->generateUrl('config_permissions'));
		}


		$aPermissions = array();

		foreach ($this->okt->getPerms() as $k=>$v)
		{
			if (!is_array($v))
			{
				if (!isset($aPermissions['others']))
				{
					$aPermissions['others'] = array(
						'libelle' => '',
						'perms' => array()
					);
				}

				if ($this->okt->checkPerm($k)) {
					$aPermissions['others']['perms'][$k] = $v;
				}
			}
			else {
				$aPermissions[$k] = array(
					'libelle' => $v['libelle'],
					'perms' => array()
				);

				foreach ($v['perms'] as $perm=>$libelle)
				{
					if ($this->okt->checkPerm($perm)) {
						$aPermissions[$k]['perms'][$perm] = $libelle;
					}
				}
			}
		}

		asort($aPermissions);

		return $this->render('Config/Permissions', array(
			'aGroups' => $aGroups,
			'aPerms' => $aPerms,
			'aPermissions' => $aPermissions
		));
	}
}