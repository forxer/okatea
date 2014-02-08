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
	 * @var integer
	 */
	const UNVERIFIED = 0;

	/**
	 * Group identifier of super-administrator users.
	 * @var integer
	 */
	const SUPERADMIN = 1;

	/**
	 * Group identifier of administrator users.
	 * @var integer
	 */
	const ADMIN = 2;

	/**
	 * Group identifier of guest users.
	 * @var integer
	 */
	const GUEST = 3;

	/**
	 * Group identifier of member users.
	 * @var integer
	 */
	const MEMBER = 4;

	/**
	 * Okatea application instance.
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * The database manager instance.
	 * @var object
	 */
	protected $db;

	/**
	 * The errors manager instance.
	 * @var object
	 */
	protected $error;

	/**
	 * Core users table
	 * @var string
	 */
	protected $t_users;

	/**
	 * Core users groups table
	 * @var string
	 */
	protected $t_groups;

	public function __construct($okt)
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;

		$this->t_users = $this->db->prefix.'core_users';
		$this->t_groups = $this->db->prefix.'core_users_groups';
	}

	/**
	 * Retourne les informations de plusieurs groupes
	 *
	 * @param $param
	 * @param $bCountOnly
	 * @return recordset
	 */
	public function getGroups(array $aParams = array(), $bCountOnly = false)
	{
		$sReqPlus = '';

		if (isset($aParams['group_id']))
		{
			if (is_array($aParams['group_id']))
			{
				$aParams['group_id'] = array_map('intval',$aParams['group_id']);
				$sReqPlus .= 'AND g.group_id IN ('.implode(',',$aParams['group_id']).') ';
			}
			else {
				$sReqPlus .= 'AND g.group_id='.(integer)$aParams['group_id'].' ';
			}
		}

		if (!empty($aParams['group_id_not']))
		{
			if (is_array($aParams['group_id_not']))
			{
				$aParams['group_id_not'] = array_map('intval',$aParams['group_id_not']);
				$sReqPlus .= 'AND g.group_id NOT IN ('.implode(',',$aParams['group_id_not']).') ';
			}
			else {
				$sReqPlus .= 'AND g.group_id<>'.(integer)$aParams['group_id_not'].' ';
			}
		}

		if (!empty($aParams['title'])) {
			$sReqPlus .= 'AND g.title=\''.$this->db->escapeStr($aParams['title']).'\' ';
		}

		if ($bCountOnly)
		{
			$sQuery =
			'SELECT COUNT(g.group_id) AS num_groups '.
			'FROM '.$this->t_groups.' AS g '.
				'LEFT JOIN '.$this->t_users.' AS u ON u.group_id=g.group_id '.
			'WHERE '.$sReqPlus;
		}
		else {
			$sQuery =
			'SELECT g.group_id, g.title, g.perms, count(u.id) AS num_users '.
			'FROM '.$this->t_groups.' AS g '.
				'LEFT JOIN '.$this->t_users.' AS u ON u.group_id=g.group_id '.
			'WHERE 1 '.
			$sReqPlus.' '.
			'GROUP BY g.group_id ';

			if (!empty($aParams['order'])) {
				$sQuery .= 'ORDER BY '.$aParams['order'].' ';
			}
			else {
				$sQuery .= 'ORDER BY g.group_id ASC ';
			}

			if (!empty($aParams['limit'])) {
				$sQuery .= 'LIMIT '.$aParams['limit'].' ';
			}
		}

		if (($rs = $this->db->select($sQuery)) === false) {
			return new Recordset(array());
		}

		if ($bCountOnly) {
			return (integer)$rs->num_groups;
		}
		else {
			return $rs;
		}
	}

	/**
	 * Retourne les infos d'un groupe donné.
	 *
	 * @param $group
	 * @return recordset
	 */
	public function getGroup($mGroupId)
	{
		$aParams = array();

		if (Utilities::isInt($mGroupId)) {
			$aParams['group_id'] = $mGroupId;
		}
		else {
			$aParams['title'] = $mGroupId;
		}

		return $this->getGroups($aParams);
	}

	/**
	 * Indique si un groupe existe
	 *
	 * @param $id
	 * @return boolean
	 */
	public function groupExists($id)
	{
		if ($this->getGroup($id)->isEmpty()) {
			return false;
		}

		return true;
	}

	/**
	 * Ajout d'un groupe
	 *
	 * @param $title
	 * @return integer
	 */
	public function addGroup($title)
	{
		$sQuery =
		'INSERT INTO '.$this->t_groups.' ( '.
		'title'.
		') VALUES ( '.
		'\''.$this->db->escapeStr($title).'\' '.
		'); ';

		if (!$this->db->execute($sQuery)) {
			return false;
		}

		return $this->db->getLastID();
	}

	/**
	 * Mise à jour d'un groupe
	 *
	 * @param $iGroupId
	 * @param $title
	 * @return boolean
	 */
	public function updGroup($iGroupId, $title)
	{
		if (!$this->groupExists($iGroupId)) {
			return false;
		}

		$sQuery =
		'UPDATE '.$this->t_groups.' SET '.
		'title=\''.$this->db->escapeStr($title).'\' '.
		'WHERE group_id='.(integer)$iGroupId;

		if (!$this->db->execute($sQuery)) {
			return false;
		}

		return true;
	}

	public function updGroupPerms($iGroupId, $perms)
	{
		if (!$this->groupExists($iGroupId)) {
			return false;
		}

		if (is_array($perms)) {
			$perms = serialize($perms);
		}

		$sQuery =
		'UPDATE '.$this->t_groups.' SET '.
		'perms=\''.$this->db->escapeStr($perms).'\' '.
		'WHERE group_id='.(integer)$iGroupId;

		if (!$this->db->execute($sQuery)) {
			return false;
		}

		return true;
	}

	/**
	 * Suppression d'un groupe.
	 *
	 * @param $id
	 * @return boolean
	 */
	public function deleteGroup($iGroupId)
	{
		if (!$this->groupExists($iGroupId)) {
			return false;
		}

		$oUsers = new Users($this->okt);
		$nbUser = $oUsers->getUsers(array('group_id'=>$iGroupId),true);

		if ($nbUser > 0)
		{
			$this->error->set(__('c_c_users_error_users_in_group_cannot_remove'));
			return false;
		}
		else {
			$sQuery =
			'DELETE FROM '.$this->t_groups.' '.
			'WHERE group_id='.(integer)$iGroupId;

			if (!$this->db->execute($sQuery)) {
				return false;
			}

			$this->db->optimize($this->t_groups);

			return true;
		}
	}
}
