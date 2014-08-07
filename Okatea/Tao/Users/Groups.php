<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Users;

use Okatea\Tao\Database\Recordset;
use Okatea\Tao\Misc\Utilities;

class Groups
{
	/**
	 * Group identifier of unverified users.
	 *
	 * @var integer
	 */
	const UNVERIFIED = 0;

	/**
	 * Group identifier of super-administrator users.
	 *
	 * @var integer
	 */
	const SUPERADMIN = 1;

	/**
	 * Group identifier of administrator users.
	 *
	 * @var integer
	 */
	const ADMIN = 2;

	/**
	 * Group identifier of guest users.
	 *
	 * @var integer
	 */
	const GUEST = 3;

	/**
	 * Group identifier of member users.
	 *
	 * @var integer
	 */
	const MEMBER = 4;

	public static $native = [
		self::UNVERIFIED,
		self::SUPERADMIN,
		self::ADMIN,
		self::GUEST,
		self::MEMBER
	];

	/**
	 * Okatea application instance.
	 *
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * Core users table.
	 *
	 * @var string
	 */
	protected $sUsersTable;

	/**
	 * Core users groups table.
	 *
	 * @var string
	 */
	protected $sGroupsTable;

	/**
	 * Core users groups locales table.
	 *
	 * @var string
	 */
	protected $sGroupsL10nTable;

	public function __construct($okt)
	{
		$this->okt = $okt;

		$this->sUsersTable = $okt['config']->database_prefix . 'core_users';
		$this->sGroupsTable = $okt['config']->database_prefix . 'core_users_groups';
		$this->sGroupsL10nTable = $okt['config']->database_prefix . 'core_users_groups_locales';
	}

	/**
	 * Returns a list of users groups ​​according to given parameters.
	 *
	 * @param array	$aParams
	 * @param boolean $bCountOnly
	 * @return array|integer
	 */
	public function getGroups(array $aParams = [], $bCountOnly = false)
	{
		$queryBuilder = $this->okt['db']->createQueryBuilder();

		$queryBuilder
			->from($this->sGroupsTable, 'g')
			->leftJoin('g', $this->sUsersTable, 'u', 'g.group_id = u.group_id')
			->leftJoin('g', $this->sGroupsL10nTable, 'gl', 'g.group_id = gl.group_id')
			->where('true = true');

		if (isset($aParams['group_id']))
		{
			if (is_array($aParams['group_id']))
			{
				$queryBuilder->andWhere(
					$queryBuilder->expr()->in('g.group_id', array_map('intval', $aParams['group_id']))
				);
			}
			else
			{
				$queryBuilder
					->andWhere('g.group_id = :group_id')
					->setParameter('group_id', (integer)$aParams['group_id']);
			}
		}

		if (isset($aParams['group_id_not']))
		{
			if (is_array($aParams['group_id_not']))
			{
				$queryBuilder->andWhere(
					$queryBuilder->expr()->notIn('g.group_id', array_map('intval', $aParams['group_id_not']))
				);
			}
			else
			{
				$queryBuilder
					->andWhere('g.group_id <> :group_id_not')
					->setParameter('group_id_not', (integer)$aParams['group_id_not']);
			}
		}

		if (!empty($aParams['title']))
		{
			$queryBuilder
				->andWhere('gl.title = :title')
				->setParameter('title', $aParams['title']);
		}

		if (!empty($aParams['language']))
		{
			$queryBuilder
				->andWhere('gl.language = :language')
				->setParameter('language', $aParams['language']);
		}

		if ($bCountOnly)
		{
			$queryBuilder->select('COUNT(g.group_id) AS num_groups');
		}
		else
		{
			$queryBuilder
				->select('g.group_id', 'g.perms', 'gl.title', 'gl.description', 'count(u.id) AS num_users')
				->groupBy('g.group_id')
			;

			if (!empty($aParams['order']) && !empty($aParams['order_direction'])) {
				$queryBuilder->orderBy($aParams['order'], $aParams['order_direction']);
			}
			else {
				$queryBuilder->orderBy('g.group_id', 'ASC');
			}
		}

		if ($bCountOnly) {
			return (integer) $queryBuilder->execute()->fetchColumn();
		}

		return $queryBuilder->execute()->fetchAll();
	}

	/**
	 * Returns information of a given group.
	 *
	 * @param mixed $mGroupId
	 * @return recordset
	 */
	public function getGroup($mGroupId)
	{
		$aParams = [];

		if (Utilities::isInt($mGroupId)) {
			$aParams['group_id'] = $mGroupId;
		}
		else {
			$aParams['title'] = $mGroupId;
		}

		$aGroup = $this->getGroups($aParams);

		return isset($aGroup[0]) ? $aGroup[0] : null;
	}

	/**
	 * Indicates whether a specified group exists.
	 *
	 * @param integer $iGroupId
	 * @return boolean
	 */
	public function groupExists($iGroupId)
	{
		return $this->getGroup($iGroupId) ? true : false;
	}

	/**
	 * Returns the internationalized data of a given group.
	 *
	 * @param integer $iItemId
	 * @return Recordset
	 */
	public function getGroupL10n($iGroupId)
	{
		$queryBuilder = $this->okt['db']->createQueryBuilder();

		$queryBuilder
			->select('*')
			->from($this->sGroupsL10nTable)
			->where('group_id = :group_id')
			->setParameter('group_id', (integer) $iGroupId)
		;

		return $queryBuilder->execute()->fetchAll();
	}

	/**
	 * Indicates whether the internationalized data for a given group and a given language exist.
	 *
	 * @param integer $iGroupId
	 * @param string $sLanguage
	 * @return boolean
	 */
	public function groupL10nExists($iGroupId, $sLanguage)
	{
		$queryBuilder = $this->okt['db']->createQueryBuilder();

		$queryBuilder
			->select('COUNT(group_id)')
			->from($this->sGroupsL10nTable)
			->where('group_id = :group_id')
			->andWhere('language = :language')
			->setParameter('group_id', (integer) $iGroupId)
			->setParameter('language', $sLanguage)
		;

		$iNumRow = (integer) $queryBuilder->execute()->fetchColumn();

		return $iNumRow >= 1;
	}

	/**
	 * Add a group.
	 *
	 * @param array $aData
	 * @return integer
	 */
	public function addGroup(array $aData)
	{
		$this->okt['db']->insert($this->sGroupsTable, []);

		$iGroupId = $this->okt['db']->lastInsertId();

		$this->setGroupL10n($iGroupId, $aData['locales']);

		$this->updGroupPerms($iGroupId, $aData['perms']);

		return $iGroupId;
	}

	/**
	 * Update a group.
	 *
	 * @param integer $iGroupId
	 * @param array $aData
	 * @return boolean
	 */
	public function updGroup($iGroupId, array $aData)
	{
		if (!$this->groupExists($iGroupId))
		{
			$this->okt['instantMessages']->error(sprintf(__('c_c_users_error_group_%s_not_exists'), $iGroupId));
			return false;
		}

		$this->setGroupL10n($iGroupId, $aData['locales']);

		$this->updGroupPerms($iGroupId, $aData['perms']);

		return true;
	}

	/**
	 * Update permissions of a given group.
	 *
	 * @param integer $iGroupId
	 * @param array $aPerms
	 * @return boolean
	 */
	public function updGroupPerms($iGroupId, $aPerms = null)
	{
		if (!$this->groupExists($iGroupId))
		{
			$this->okt['instantMessages']->error(sprintf(__('c_c_users_error_group_%s_not_exists'), $iGroupId));
			return false;
		}

		if (is_array($aPerms))
		{
			$this->okt['db']->update($this->sGroupsTable,
				[
					'perms' => json_encode($aPerms)
				],
				[
					'group_id' => $iGroupId
				]
			);
		}

		return true;
	}

	/**
	 * Delete a given group.
	 *
	 * @param integer $iGroupId
	 * @return boolean
	 */
	public function deleteGroup($iGroupId)
	{
		$aGroup = $this->getGroup($iGroupId);

		if (!$aGroup)
		{
			$this->okt['instantMessages']->error(sprintf(__('c_c_users_error_group_%s_not_exists'), $iGroupId));
			return false;
		}
		elseif (in_array($iGroupId, self::$native))
		{
			$this->okt['instantMessages']->error(__('c_c_users_error_cannot_remove_group'));
			return false;
		}
		elseif ($aGroup['num_users'] > 0)
		{
			$this->okt['instantMessages']->error(__('c_c_users_error_users_in_group_cannot_remove'));
			return false;
		}

		$aDeleteParam = [
			'group_id' => (integer) $iGroupId
		];

		$this->okt['db']->delete($this->sGroupsL10nTable, $aDeleteParam);

		$this->okt['db']->delete($this->sGroupsTable, $aDeleteParam);

		return true;
	}

	/**
	 * Add/Edit internationalized data of a given group.
	 *
	 * @param integer $iGroupId
	 * @param array $aData
	 */
	protected function setGroupL10n($iGroupId, $aData)
	{
		foreach ($this->okt['languages']->getList() as $aLanguage)
		{
			if ($this->groupL10nExists($iGroupId, $aLanguage['code']))
			{
				$this->okt['db']->update($this->sGroupsL10nTable,
					$aData[$aLanguage['code']],
					[
						'group_id' => (integer)$iGroupId,
						'language' => $aLanguage['code']
					]
				);
			}
			else
			{
				$aData[$aLanguage['code']]['group_id'] = $iGroupId;
				$aData[$aLanguage['code']]['language'] = $aLanguage['code'];

				$this->okt['db']->insert($this->sGroupsL10nTable, $aData[$aLanguage['code']]);
			}
		}
	}
}
