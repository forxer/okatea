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

class Users
{
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

	protected $t_users;

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
	 * Renvoie les données concernant un ou plusieurs utilisateurs.
	 * La méthode renvoie false si elle échoue.
	 *
	 * @param	array	params		Les paramètres
	 * @param	boolean	count_only	Permet de ne retourner que le compte
	 * @return	Recordset
	 */
	public function getUsers(array $aParams = array(), $bCountOnly=false)
	{
		$sReqPlus = 'WHERE 1 ';

		if (isset($aParams['id'])) {
			$sReqPlus .= 'AND u.id='.(integer)$aParams['id'].' ';
		}

		if (isset($aParams['username'])) {
			$sReqPlus .= 'AND u.username=\''.$this->db->escapeStr($aParams['username']).'\' ';
		}

		if (isset($aParams['active']))
		{
			if ($aParams['active'] == 0) {
				$sReqPlus .= 'AND u.active=0 ';
			}
			elseif ($aParams['active'] == 1) {
				$sReqPlus .= 'AND u.active=1 ';
			}
			elseif ($aParams['active'] == 2) {
				$sReqPlus .= '';
			}
		}

		if (isset($aParams['group_id']))
		{
			if (is_array($aParams['group_id']))
			{
				$aParams['group_id'] = array_map('intval',$aParams['group_id']);
				$sReqPlus .= 'AND u.group_id IN ('.implode(',',$aParams['group_id']).') ';
			}
			else {
				$sReqPlus .= 'AND u.group_id='.(integer)$aParams['group_id'].' ';
			}
		}

		if (!empty($aParams['group_id_not']))
		{
			if (is_array($aParams['group_id_not']))
			{
				$aParams['group_id_not'] = array_map('intval',$aParams['group_id_not']);
				$sReqPlus .= 'AND u.group_id NOT IN ('.implode(',',$aParams['group_id_not']).') ';
			}
			else {
				$sReqPlus .= 'AND u.group_id<>'.(integer)$aParams['group_id_not'].' ';
			}
		}

		if (!empty($aParams['search']))
		{
			$aWords = \text::splitWords($aParams['search']);

			if (!empty($aWords))
			{
				foreach ($aWords as $i=>$w)
				{
					$aWords[$i] =
					'u.username LIKE \'%'.$this->db->escapeStr($w).'%\' OR '.
					'u.lastname LIKE \'%'.$this->db->escapeStr($w).'%\' OR '.
					'u.firstname LIKE \'%'.$this->db->escapeStr($w).'%\' OR '.
					'u.email LIKE \'%'.$this->db->escapeStr($w).'%\' ';
				}
				$sReqPlus .= ' AND '.implode(' AND ',$aWords).' ';
			}
		}

		if ($bCountOnly)
		{
			$sQuery =
			'SELECT COUNT(u.id) AS num_users '.
			'FROM '.$this->t_users.' AS u '.
			$sReqPlus;
		}
		else
		{
			$sQuery =
			'SELECT u.*, g.* '.
			'FROM '.$this->t_users.' AS u '.
				'LEFT JOIN '.$this->t_groups.' AS g ON g.group_id=u.group_id '.
			$sReqPlus;

			if (isset($aParams['order'])) {
				$sQuery .= 'ORDER BY '.$aParams['order'].' ';
			}
			else {
				$sQuery .= 'ORDER BY u.username DESC ';
			}

			if (isset($aParams['limit'])) {
				$sQuery .= 'LIMIT '.$aParams['limit'].' ';
			}
		}

		if (($rs = $this->db->select($sQuery)) === false) {
			return new Recordset(array());
		}

		if ($bCountOnly) {
			return (integer)$rs->num_users;
		}
		else {
			return $rs;
		}
	}

	/**
	 * Retourne les infos d'un utilisateur donné.
	 * Le paramètre user peut être l'identifiant numérique
	 * ou le nom d'utilisateur.
	 *
	 * @param $mUserId
	 * @return Recordset
	 */
	public function getUser($mUserId)
	{
		$aParams = array();

		if (Utilities::isInt($mUserId)) {
			$aParams['id'] = $mUserId;
		}
		else {
			$aParams['username'] = $mUserId;
		}

		return $this->getUsers($aParams);
	}

	/**
	 * Vérifie l'existence d'un utilisateur
	 *
	 * @param $mUserId
	 * @return boolean
	 */
	public function userExists($mUserId)
	{
		if ($this->getUser($mUserId)->isEmpty()) {
			return false;
		}

		return true;
	}

	/**
	 * Vérifie qu'il n'y a pas de flood à l'inscription en vérifiant l'IP.
	 * @return boolean
	 */
	public function checkRegistrationFlood()
	{
		$sQuery =
		'SELECT 1 FROM '.$this->t_users.' AS u '.
		'WHERE u.registration_ip=\''.$this->db->escapeStr($this->okt->request->getClientIp()).'\' '.
		'AND u.registered>'.(time() - 3600);

		if (($rs = $this->db->select($sQuery)) === false) {
			return false;
		}

		if (!$rs->isEmpty()) {
			return false;
		}

		return true;
	}

	/**
	 * Vérifie la validité d'un nom d'utilisateur.
	 *
	 * @param $username
	 * @return void
	 */
	public function checkUsername($aParams=array())
	{
		$username = !empty($aParams['username']) ? $aParams['username'] : null;
		$username = preg_replace('#\s+#s', ' ', $username);

		if (mb_strlen($username) < 2) {
			$this->error->set(__('c_c_users_error_username_too_short'));
		}
		elseif (mb_strlen($username) > 255) {
			$this->error->set(__('c_c_users_error_username_too_long'));
		}
		elseif (mb_strtolower($username) == 'guest') {
			$this->error->set(__('c_c_users_error_reserved_username'));
		}
		elseif (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $username) || preg_match('/((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))/', $username)) {
			$this->error->set(__('c_c_users_error_reserved_username'));
		}
		elseif ((strpos($username, '[') !== false || strpos($username, ']') !== false) && strpos($username, '\'') !== false && strpos($username, '"') !== false) {
			$this->error->set(__('c_c_users_error_forbidden_characters'));
		}
		elseif ($this->userExists($username))
		{
			$dupe = true;

			if (!empty($aParams['id']))
			{
				$user = $this->getUser($aParams['id']);

				if ($user->username == $username) {
					$dupe = false;
				}
			}

			if ($dupe)
			{
				if ($this->config->merge_username_email) {
					$this->error->set(__('c_c_users_error_email_already_exist'));
				}
				else {
					$this->error->set(__('c_c_users_error_username_already_exist'));
				}
			}
		}
	}

	/**
	 * Vérifie l'email
	 *
	 * @param $aParams
	 * @return void
	 */
	public function checkEmail($aParams=array())
	{
		if (empty($aParams['email'])) {
			$this->error->set(__('c_c_users_must_enter_email_address'));
		}

		$this->isEmail($aParams['email']);
	}

	/**
	 * Vérifie si l'email est valide
	 *
	 * @param $sEmail
	 * @return void
	 */
	public function isEmail($sEmail)
	{
		if (!\text::isEmail($sEmail)) {
			$this->error->set(sprintf(__('c_c_error_invalid_email'), Utilities::escapeHTML($sEmail)));
		}
	}

	/**
	 * Vérifie le mot de passe et la confirmation du mot de passe.
	 *
	 * @param $aParams
	 * @return void
	 */
	public function checkPassword($aParams=array())
	{
		if (empty($aParams['password'])) {
			$this->error->set(__('c_c_users_must_enter_password'));
		}
		elseif (mb_strlen($aParams['password']) < 4) {
			$this->error->set(__('c_c_users_must_enter_password_of_at_least_4_characters'));
		}
		elseif (empty($aParams['password_confirm'])) {
			$this->error->set(__('c_c_users_must_confirm_password'));
		}
		elseif ($aParams['password'] != $aParams['password_confirm']) {
			$this->error->set(__('c_c_users_error_passwords_do_not_match'));
		}
	}

	/**
	 * Ajout d'un utilisateur
	 *
	 * @param $aParams
	 * @return integer
	 */
	public function addUser($aParams=array())
	{
		$this->checkUsername($aParams);

		$this->checkPassword($aParams);

		$this->checkEmail($aParams);

		if (!$this->error->isEmpty()) {
			return false;
		}

		if ($this->config->validate_users_registration == 1) {
			$aParams['group_id'] = 0;
		}
		elseif (empty($aParams['group_id']) || !$this->groupExists($aParams['group_id'])) {
			$aParams['group_id'] = $this->config->default_group;
		}

		$password_hash = password_hash($aParams['password'], PASSWORD_DEFAULT);
		$iTime= time();

		$sQuery =
		'INSERT INTO '.$this->t_users.' ( '.
		'group_id, civility, active, username, lastname, firstname, password, salt, email, '.
		'timezone, language, registered, registration_ip, last_visit '.
		') VALUES ( '.
		(integer)$aParams['group_id'].', '.
		(integer)$aParams['civility'].', '.
		(integer)$aParams['active'].', '.
		'\''.$this->db->escapeStr($aParams['username']).'\', '.
		'\''.$this->db->escapeStr($aParams['lastname']).'\', '.
		'\''.$this->db->escapeStr($aParams['firstname']).'\', '.
		'\''.$this->db->escapeStr($password_hash).'\', '.
		'\''.$this->db->escapeStr(Utilities::random_key(12)).'\', '.
		'\''.$this->db->escapeStr($aParams['email']).'\', '.
		'\''.$this->db->escapeStr($aParams['timezone']).'\', '.
		'\''.$this->db->escapeStr($aParams['language']).'\', '.
		$iTime.', '.
		(!empty($aParams['registration_ip']) ? '\''.$this->db->escapeStr($aParams['registration_ip']).'\', ' : '\'0.0.0.0\', ').
		$iTime.
		'); ';

		if (!$this->db->execute($sQuery)) {
			return false;
		}

		$iNewId = $this->db->getLastID();

		return $iNewId;
	}

	/**
	 * Mise à jour d'une page
	 *
	 * @param integer $id
	 * @param string $title
	 * @param string $code
	 * @return boolean
	 */
	public function updUser($aParams=array())
	{
		if (!$this->userExists($aParams['id'])) {
			return false;
		}

		$sql = array();

		if (isset($aParams['username']))
		{
			$this->checkUsername($aParams);

			$sql[] = 'username=\''.$this->db->escapeStr($aParams['username']).'\'';
		}

		if (isset($aParams['group_id'])) {
			$sql[] = 'group_id='.(integer)$aParams['group_id'];
		}

		if (isset($aParams['civility'])) {
			$sql[] = 'civility='.(integer)$aParams['civility'];
		}

		if (isset($aParams['active'])) {
			$sql[] = 'active='.(integer)$aParams['active'];
		}

		if (isset($aParams['lastname'])) {
			$sql[] = 'lastname=\''.$this->db->escapeStr($aParams['lastname']).'\'';
		}

		if (isset($aParams['firstname'])) {
			$sql[] = 'firstname=\''.$this->db->escapeStr($aParams['firstname']).'\'';
		}

		if (isset($aParams['email'])) {
			$this->checkEmail($aParams);
			$sql[] = 'email=\''.$this->db->escapeStr($aParams['email']).'\'';
		}

		if (isset($aParams['language'])) {
			$sql[] = 'language=\''.$this->db->escapeStr($aParams['language']).'\'';
		}

		if (isset($aParams['timezone'])) {
			$sql[] = 'timezone=\''.$this->db->escapeStr($aParams['timezone']).'\'';
		}

		if (!$this->error->isEmpty()) {
			return false;
		}

		$sQuery =
		'UPDATE '.$this->t_users.' SET '.
		implode(', ',$sql).' '.
		'WHERE id='.(integer)$aParams['id'];

		if (!$this->db->execute($sQuery)) {
			return false;
		}

		return true;
	}

	/**
	 * Modification du mot de passe d'un utilisateur
	 *
	 * @param $aParams
	 * @return boolean
	 */
	public function changeUserPassword($aParams=array())
	{
		$this->checkPassword($aParams);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$password_hash = password_hash($aParams['password'], PASSWORD_DEFAULT);

		$sQuery =
		'UPDATE '.$this->t_users.' SET '.
		'password=\''.$this->db->escapeStr($password_hash).'\', '.
		'salt=\''.$this->db->escapeStr(Utilities::random_key(12)).'\' '.
		'WHERE id='.(integer)$aParams['id'];

		if (!$this->db->execute($sQuery)) {
			return false;
		}

		return true;
	}

	/**
	 * Suppression d'un utilisateur.
	 *
	 * @param $id
	 * @return boolean
	 */
	public function deleteUser($id)
	{
		$rsUser = $this->getUsers(array('id'=>$id));

		if ($rsUser->isEmpty())
		{
			$this->error->set(sprintf(__('c_c_users_error_user_%s_not_exists'), $id));
			return false;
		}

		# si on veut supprimer un super-admin alors il faut vérifier qu'il y en as d'autres
		if ($rsUser->group_id == Groups::SUPERADMIN)
		{
			$iCountSudo = $this->getUsers(array('group_id' => Groups::SUPERADMIN), true);

			if ($iCountSudo < 2)
			{
				$this->error->set(__('c_c_users_error_cannot_remove_last_super_administrator'));
				return false;
			}
		}

		# si on veut supprimer un admin alors il faut vérifier qu'il y en as d'autres
		if ($rsUser->group_id == Groups::ADMIN)
		{
			$iCountAdmin = $this->getUsers(array('group_id' => Groups::ADMIN), true);

			if ($iCountAdmin < 2)
			{
				$this->error->set(__('c_c_users_error_cannot_remove_last_administrator'));
				return false;
			}
		}

		$sQuery =
		'DELETE FROM '.$this->t_users.' '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($sQuery)) {
			return false;
		}

		$this->db->optimize($this->t_users);

		# delete user custom fields
		if ($this->okt->config->users_custom_fields_enabled) {
			$this->fields->delUserValue($id);
		}

		# delete user directory
		$user_dir = $this->upload_dir.'/'.$id;

		if (\files::isDeletable($user_dir)) {
			\files::deltree($user_dir);
		}

		return true;
	}

	/**
		* Switch le statut d'un utilisateur donné
		*
		* @param integer $iUserId
		* @return boolean
		*/
	public function switchUserStatus($iUserId)
	{
		if (!$this->userExists($iUserId)) {
			$this->error->set(sprintf(__('c_c_users_error_user_%s_not_exists'), $iUserId));
			return false;
		}

		$sSqlQuery =
		'UPDATE '.$this->t_users.' SET '.
		'active = 1-active '.
		'WHERE id='.(integer)$iUserId;

		if (!$this->db->execute($sSqlQuery)) {
			return false;
		}

		return true;
	}

	/**
	* Définit le statut d'un utilisateur donné
	*
	* @param integer $iUserId
	* @param integer $iActive
	* @return boolean
	*/
	public function setUserStatus($iUserId, $iActive)
	{
		$iActive = intval($iActive);

		$rsUser = $this->getUsers(array('id' => $iUserId));

		if ($rsUser->isEmpty())
		{
			$this->error->set(sprintf(__('c_c_users_error_user_%s_not_exists'), $iUserId));
			return false;
		}

		# si on veut désactiver un super-admin alors il faut vérifier qu'il y en as d'autres
		if ($iActive == 0 && $rsUser->group_id == Groups::SUPERADMIN)
		{
			$iCountSudo = $this->getUsers(array('group_id' => Groups::SUPERADMIN, 'active' => 1), true);

			if ($iCountSudo < 2)
			{
				$this->error->set(__('c_c_users_error_cannot_disable_last_super_administrator'));
				return false;
			}
		}

		# si on veut désactiver un admin alors il faut vérifier qu'il y en as d'autres
		if ($iActive == 0 && $rsUser->group_id == Groups::ADMIN)
		{
			$iCountAdmin = $this->getUsers(array('group_id' => Groups::ADMIN, 'active' => 1), true);

			if ($iCountAdmin < 2)
			{
				$this->error->set(__('c_c_users_error_cannot_disable_last_administrator'));
				return false;
			}
		}

		$sSqlQuery =
		'UPDATE '.$this->t_users.' SET '.
		'active = '.($iActive == 1 ? 1 : 0).' '.
		'WHERE id='.(integer)$iUserId;

		if (!$this->db->execute($sSqlQuery)) {
			return false;
		}

		return true;
	}

	/**
	 * Static function that returns user's common name given to his
	 * username, lastname, firstname and displayname.
	 *
	 * @param string $sUsername			User name
	 * @param string $sLastname			User's last name
	 * @param string $sFirstname		User's first name
	 * @param string $sDisplayName		User's display name
	 * @return string
	 */
	public static function getUserCN($sUsername, $sLastname, $sFirstname, $sDisplayName=null)
	{
	    if (!empty($sDisplayName)) {
	        return $sDisplayName;
	    }
	
	    if (!empty($sLastname))
	    {
	        if (!empty($sFirstname)) {
	            return $sFirstname.' '.$sLastname;
	        }
	
	        return $sLastname;
	    }
	    elseif (!empty($sFirstname)) {
	        return $sFirstname;
	    }
	
	    return $sUsername;
	}
}
