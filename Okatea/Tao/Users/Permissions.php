<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Users;

class Permissions
{
	/**
	 * Okatea application instance.
	 *
	 * @var Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * The stack that contains the list of permissions.
	 *
	 * @var array
	 */
	protected $aPermsStack = [];

	public function __construct($okt)
	{
		$this->okt = $okt;
	}

	/**
	 * Return permissions stack.
	 *
	 * @return array
	 */
	public function getPerms()
	{
		return $this->aPermsStack;
	}

	/**
	 * Add a permission.
	 *
	 * @param string $sId Permission identifier
	 * @param string $sTitle Permission intitle
	 * @param string $sGroupId Permission group identifier (null)
	 * @return void
	 */
	public function addPerm($sId, $sTitle, $sGroupId = null)
	{
		if ($sGroupId && !empty($this->aPermsStack[$sGroupId])) {
			$this->aPermsStack[$sGroupId]['perms'][$sId] = $sTitle;
		}
		else {
			$this->aPermsStack[$sId] = $sTitle;
		}
	}
	/**
	 * Ajout d'un groupe de permissions.
	 *
	 * @param string $sGroupId Permission group identifier
	 * @param string $sTitle Group intitle
	 * @return void
	 */
	public function addPermGroup($sGroupId, $sTitle)
	{
		$this->aPermsStack[$sGroupId] = [
			'title' => $sTitle,
			'perms' => []
		];
	}


	public function getPermsForDisplay()
	{
		$aPermissions = [];

		foreach ($this->aPermsStack as $k => $v)
		{
			if (!is_array($v))
			{
				if (!isset($aPermissions['others']))
				{
					$aPermissions['others'] = [
						'title' => '',
						'perms' => []
					];
				}

				if ($this->okt['visitor']->checkPerm($k)) {
					$aPermissions['others']['perms'][$k] = $v;
				}
			}
			else
			{
				$aPermissions[$k] = [
					'title' => $v['title'],
					'perms' => []
				];

				foreach ($v['perms'] as $perm => $libelle)
				{
					if ($this->okt['visitor']->checkPerm($perm)) {
						$aPermissions[$k]['perms'][$perm] = $libelle;
					}
				}
			}
		}

		asort($aPermissions);

		return $aPermissions;
	}

}
