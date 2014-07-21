<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Users;

use RuntimeException;
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
	 * The database manager instance.
	 *
	 * @var object
	 */
	protected $oDb;

	/**
	 * The errors manager instance.
	 *
	 * @var object
	 */
	protected $oError;

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
		$this->oDb = $okt->db;
		$this->oError = $okt->error;

		$this->sUsersTable = $this->oDb->prefix . 'core_users';
		$this->sGroupsTable = $this->oDb->prefix . 'core_users_groups';
		$this->sGroupsL10nTable = $this->oDb->prefix . 'core_users_groups_locales';
	}

	/**
	 * Retourne les informations de plusieurs groupes
	 *
	 * @param
	 *        	$param
	 * @param
	 *        	$bCountOnly
	 * @return recordset
	 */
	public function getGroups(array $aParams = [], $bCountOnly = false)
	{
		$sReqPlus = '';

		if (isset($aParams['group_id']))
		{
			if (is_array($aParams['group_id']))
			{
				$aParams['group_id'] = array_map('intval', $aParams['group_id']);
				$sReqPlus .= 'AND g.group_id IN (' . implode(',', $aParams['group_id']) . ') ';
			}
			else
			{
				$sReqPlus .= 'AND g.group_id=' . (integer) $aParams['group_id'] . ' ';
			}
		}

		if (! empty($aParams['group_id_not']))
		{
			if (is_array($aParams['group_id_not']))
			{
				$aParams['group_id_not'] = array_map('intval', $aParams['group_id_not']);
				$sReqPlus .= 'AND g.group_id NOT IN (' . implode(',', $aParams['group_id_not']) . ') ';
			}
			else
			{
				$sReqPlus .= 'AND g.group_id<>' . (integer) $aParams['group_id_not'] . ' ';
			}
		}

		if (! empty($aParams['title']))
		{
			$sReqPlus .= 'AND gl.title=\'' . $this->oDb->escapeStr($aParams['title']) . '\' ';
		}

		if (! empty($aParams['language']))
		{
			$sReqPlus .= 'AND gl.language=\'' . $this->oDb->escapeStr($aParams['language']) . '\' ';
		}

		if ($bCountOnly)
		{
			$sQuery = 'SELECT COUNT(g.group_id) AS num_groups ' . 'FROM ' . $this->sGroupsTable . ' AS g ' . 'LEFT JOIN ' . $this->sUsersTable . ' AS u ON u.group_id=g.group_id ' . 'LEFT JOIN ' . $this->sGroupsL10nTable . ' AS gl ON g.group_id=gl.group_id ' . 'WHERE ' . $sReqPlus;
		}
		else
		{
			$sQuery = 'SELECT g.group_id, g.perms, gl.title, gl.description, count(u.id) AS num_users ' . 'FROM ' . $this->sGroupsTable . ' AS g ' . 'LEFT JOIN ' . $this->sGroupsL10nTable . ' AS gl ON g.group_id=gl.group_id ' . 'LEFT JOIN ' . $this->sUsersTable . ' AS u ON u.group_id=g.group_id ' . 'WHERE 1 ' . $sReqPlus . ' ' . 'GROUP BY g.group_id ';

			if (! empty($aParams['order']))
			{
				$sQuery .= 'ORDER BY ' . $aParams['order'] . ' ';
			}
			else
			{
				$sQuery .= 'ORDER BY g.group_id ASC ';
			}

			if (! empty($aParams['limit']))
			{
				$sQuery .= 'LIMIT ' . $aParams['limit'] . ' ';
			}
		}

		if (($rs = $this->oDb->select($sQuery)) === false)
		{
			return new Recordset([]);
		}

		if ($bCountOnly)
		{
			return (integer) $rs->num_groups;
		}
		else
		{
			return $rs;
		}
	}

	/**
	 * Retourne les infos d'un groupe donné.
	 *
	 * @param mixed $mGroupId
	 * @return recordset
	 */
	public function getGroup($mGroupId)
	{
		$aParams = [];

		if (Utilities::isInt($mGroupId))
		{
			$aParams['group_id'] = $mGroupId;
		}
		else
		{
			$aParams['title'] = $mGroupId;
		}

		return $this->getGroups($aParams);
	}

	/**
	 * Indique si un groupe existe.
	 *
	 * @param integer $iGroupId
	 * @return boolean
	 */
	public function groupExists($iGroupId)
	{
		if ($this->getGroup($iGroupId)->isEmpty())
		{
			return false;
		}

		return true;
	}

	/**
	 * Retourne les localisations d'un groupe donné.
	 *
	 * @param integer $iItemId
	 * @return Recordset
	 */
	public function getGroupL10n($iGroupId)
	{
		$query = 'SELECT * FROM ' . $this->sGroupsL10nTable . ' ' . 'WHERE group_id=' . (integer) $iGroupId;

		if (($rs = $this->oDb->select($query)) === false)
		{
			$rs = new Recordset([]);
			return $rs;
		}

		return $rs;
	}

	/**
	 * Ajout d'un groupe.
	 *
	 * @param array $aData
	 * @return integer
	 */
	public function addGroup($aData)
	{
		$sQuery = 'INSERT INTO ' . $this->sGroupsTable . ' ( ' .

		') VALUES ( ' .

		'); ';

		if (! $this->oDb->execute($sQuery))
		{
			throw new RuntimeException('Unable to add group into database.');
		}

		$iGroupId = $this->oDb->getLastID();

		$this->setGroupL10n($iGroupId, $aData['locales']);

		$this->updGroupPerms($iGroupId, $aData['perms']);

		return $iGroupId;
	}

	/**
	 * Mise à jour d'un groupe.
	 *
	 * @param integer $iGroupId
	 * @param array $aData
	 * @return boolean
	 */
	public function updGroup($iGroupId, $aData)
	{
		if (! $this->groupExists($iGroupId))
		{
			$this->oError->set(sprintf(__('c_c_users_error_group_%s_not_exists'), $iGroupId));
			return false;
		}

		/*
		$sQuery =
		'UPDATE '.$this->sGroupsTable.' SET '.
			'title=\''.$this->oDb->escapeStr($title).'\' '.
		'WHERE group_id='.(integer)$iGroupId;

		if (!$this->oDb->execute($sQuery)) {
			return false;
		}
		*/

		$this->setGroupL10n($iGroupId, $aData['locales']);

		$this->updGroupPerms($iGroupId, $aData['perms']);

		return true;
	}

	/**
	 * Mise à jour des permissions d'un groupe.
	 *
	 * @param integer $iGroupId
	 * @param array $aPerms
	 * @return boolean
	 */
	public function updGroupPerms($iGroupId, $aPerms = null)
	{
		if (! $this->groupExists($iGroupId))
		{
			$this->oError->set(sprintf(__('c_c_users_error_group_%s_not_exists'), $iGroupId));
			return false;
		}

		$sQuery = 'UPDATE ' . $this->sGroupsTable . ' SET ' . 'perms=' . (is_array($aPerms) ? '\'' . $this->oDb->escapeStr(json_encode($aPerms)) . '\'' : 'NULL') . ' ' . 'WHERE group_id=' . (integer) $iGroupId;

		if (! $this->oDb->execute($sQuery))
		{
			throw new RuntimeException('Unable to update group permissions into database.');
		}

		return true;
	}

	/**
	 * Suppression d'un groupe.
	 *
	 * @param
	 *        	$iGroupId
	 * @return boolean
	 */
	public function deleteGroup($iGroupId)
	{
		$rsGroup = $this->getGroups([
			'group_id' => $iGroupId
		]);

		if ($rsGroup->isEmpty())
		{
			$this->oError->set(sprintf(__('c_c_users_error_group_%s_not_exists'), $iGroupId));
			return false;
		}
		elseif (in_array($iGroupId, self::$native))
		{
			$this->okt->error->set(__('c_c_users_error_cannot_remove_group'));
			return false;
		}
		elseif ($rsGroup->num_users > 0)
		{
			$this->oError->set(__('c_c_users_error_users_in_group_cannot_remove'));
			return false;
		}

		$sQuery = 'DELETE FROM ' . $this->sGroupsTable . ' ' . 'WHERE group_id=' . (integer) $iGroupId;

		if (! $this->oDb->execute($sQuery))
		{
			throw new RuntimeException('Unable to remove group from database.');
		}

		$this->oDb->optimize($this->sGroupsTable);

		return true;
	}

	/**
	 * Ajout/modification des textes internationnalisés d'un groupe donné.
	 *
	 * @param integer $iGroupId
	 * @param array $aData
	 */
	protected function setGroupL10n($iGroupId, $aData)
	{
		foreach ($this->okt->languages->list as $aLanguage)
		{
			$oCursor = $this->oDb->openCursor($this->sGroupsL10nTable);

			$oCursor->group_id = $iGroupId;

			$oCursor->language = $aLanguage['code'];

			foreach ($aData[$aLanguage['code']] as $k => $v)
			{
				$oCursor->$k = $v;
			}

			if (! $oCursor->insertUpdate())
			{
				throw new RuntimeException('Unable to insert group locales in database for ' . $aLanguage['code'] . ' language.');
			}
		}
	}
}
