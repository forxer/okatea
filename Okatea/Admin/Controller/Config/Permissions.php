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
		if (! $this->okt->checkPerm('permissions'))
		{
			return $this->serve401();
		}
		
		# locales
		$this->okt->l10n->loadFile($this->okt->options->locales_dir . '/%s/admin/permissions');
		
		$aGroups = array();
		$aPerms = array();
		
		$aParams = array(
			'language' => $this->okt->user->language,
			'group_id_not' => array(
				Groups::SUPERADMIN,
				Groups::GUEST
			)
		);
		
		if (! $this->okt->user->is_superadmin)
		{
			$aParams['group_id_not'][] = Groups::ADMIN;
		}
		
		$rsGroups = $this->okt->getGroups()->getGroups($aParams);
		
		while ($rsGroups->fetch())
		{
			$aGroups[$rsGroups->group_id] = $rsGroups->title;
			
			$aPerms[$rsGroups->group_id] = $rsGroups->perms ? json_decode($rsGroups->perms) : array();
		}
		
		if ($this->request->request->has('sended_form'))
		{
			$perms = $this->request->request->get('perms');
			
			foreach ($aGroups as $group_id => $group_title)
			{
				$group_perms = ! empty($perms[$group_id]) ? array_keys($perms[$group_id]) : array();
				
				$this->okt->getGroups()->updGroupPerms($group_id, $group_perms);
			}
			
			$this->page->flash->success(__('c_a_config_permissions_updated'));
			
			return $this->redirect($this->generateUrl('config_permissions'));
		}
		
		return $this->render('Config/Permissions', array(
			'aGroups' => $aGroups,
			'aPerms' => $aPerms,
			'aPermissions' => $this->okt->getPermsForDisplay()
		));
	}
}
