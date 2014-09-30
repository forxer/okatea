<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Users;

use Okatea\Tao\Database\Recordset;
use Okatea\Tao\Html\Escaper;
use Okatea\Tao\Html\Modifiers;
use Okatea\Tao\Misc\Mailer;
use Okatea\Tao\Misc\Utilities;

class Users
{
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
	 * Returns a list of users ​​according to given parameters.
	 *
	 * @param array $aParams
	 * @param boolean $bCountOnly
	 * @return array|integer
	 */
	public function getUsers(array $aParams = [], $bCountOnly = false)
	{
		$queryBuilder = $this->okt['db']->createQueryBuilder();

		$queryBuilder
			->from($this->sUsersTable, 'u')
			->leftJoin('u', $this->sGroupsTable, 'g', 'u.group_id = g.group_id')
			->leftJoin('g', $this->sGroupsL10nTable, 'gl', 'g.group_id = gl.group_id')
			->where('true = true')
			->groupBy('u.group_id')
		;

		if (!empty($aParams['id']))
		{
			$queryBuilder
				->andWhere('u.id = :id')
				->setParameter('id', (integer)$aParams['id']);
		}

		if (!empty($aParams['username']))
		{
			$queryBuilder
				->andWhere('u.username = :username')
				->setParameter('username', $aParams['username']);
		}

		if (!empty($aParams['email']))
		{
			$queryBuilder
				->andWhere('u.email = :email')
				->setParameter('email', $aParams['email']);
		}

		if (!empty($aParams['registration_ip']))
		{
			$queryBuilder
				->andWhere('u.registration_ip = :registration_ip')
				->setParameter('registration_ip', $aParams['registration_ip']);
		}

		if (isset($aParams['status']))
		{
			if ($aParams['status'] == 0) {
				$queryBuilder->andWhere('u.status = 0');
			}
			elseif ($aParams['status'] == 1) {
				$queryBuilder->andWhere('u.status = 1');
			}
		}

		if (isset($aParams['group_id']))
		{
			if (is_array($aParams['group_id']))
			{
				$queryBuilder->andWhere(
					$queryBuilder->expr()->in('u.group_id', array_map('intval', $aParams['group_id']))
				);
			}
			else
			{
				$queryBuilder
					->andWhere('u.group_id = :group_id')
					->setParameter('group_id', (integer)$aParams['group_id']);
			}
		}

		if (!empty($aParams['group_id_not']))
		{
			if (is_array($aParams['group_id_not']))
			{
				$queryBuilder->andWhere(
					$queryBuilder->expr()->notIn('u.group_id', array_map('intval', $aParams['group_id_not']))
				);
			}
			else
			{
				$queryBuilder
					->andWhere('u.group_id <> :group_id_not')
					->setParameter('group_id_not', (integer)$aParams['group_id_not']);
			}
		}

		/*
		if (!empty($aParams['search']))
		{
			$aWords = Modifiers::splitWords($aParams['search']);

			if (!empty($aWords))
			{
				foreach ($aWords as $i => $w)
				{
					$aWords[$i] =
						'u.username LIKE \'%' . $this->oDb->escapeStr($w) . '%\' OR ' .
						'u.lastname LIKE \'%' . $this->oDb->escapeStr($w) . '%\' OR ' .
						'u.firstname LIKE \'%' . $this->oDb->escapeStr($w) . '%\' OR ' .
						'u.email LIKE \'%' . $this->oDb->escapeStr($w) . '%\' ';
				}

				$sReqPlus .= ' AND ' . implode(' AND ', $aWords) . ' ';
			}
		}
		*/


		if ($bCountOnly)
		{
			$queryBuilder
				->select('COUNT(u.id) AS num_users');
		}
		else
		{
			$queryBuilder
				->select('u.*', 'g.*', 'gl.*');

			if (!empty($aParams['order']) && !empty($aParams['order_direction']))
			{
				$queryBuilder
					->orderBy($aParams['order'], $aParams['order_direction']);
			}
			else
			{
				$queryBuilder
					->orderBy('u.username', 'DESC');
			}

			if (!empty($aParams['first_result'])) {
				$queryBuilder->setFirstResult($aParams['first_result']);
			}

			if (!empty($aParams['max_result'])) {
				$queryBuilder->setMaxResults($aParams['max_result']);
			}
		}

		if ($bCountOnly) {
			return (integer) $queryBuilder->execute()->fetchColumn();
		}

		return $queryBuilder->execute()->fetchAll();
	}

	/**
	 * Returns information of a given user.
	 *
	 * @param mixed $mUserId
	 * @return array
	 */
	public function getUser($mUserId)
	{
		$aParams = [];

		if (Utilities::isInt($mUserId)) {
			$aParams['id'] = $mUserId;
		}
		else {
			$aParams['username'] = $mUserId;
		}

		$aUser = $this->getUsers($aParams);

		return isset($aUser[0]) ? $aUser[0] : null;
	}

	/**
	 * Indicates whether a specified user exists.
	 *
	 * @param mixed $mUserId
	 * @return boolean
	 */
	public function userExists($mUserId)
	{
		return $this->getUser($mUserId) ? true : false;
	}

	/**
	 * Check that there is no flood at registration by verifying the IP.
	 *
	 * @return boolean
	 */
	public function checkRegistrationFlood()
	{
		$queryBuilder = $this->okt['db']->createQueryBuilder();

		$queryBuilder
			->select('COUNT(id) AS num_users')
			->from($this->sUsersTable)
			->where('registration_ip = :registration_ip')
			->andWhere('registered > :registered')
			->setParameter('registration_ip', $this->okt['request']->getClientIp())
			->setParameter('registered', (time() - 3600));

		$iNumUser = (integer) $queryBuilder->execute()->fetchColumn();

		if ($iNumUser > 0) {
			return false;
		}

		return true;
	}

	/**
	 * Checks the validity of a username.
	 *
	 * @param array $aParams
	 * @return void
	 */
	public function checkUsername(array $aParams = [])
	{
		$username = !empty($aParams['username']) ? $aParams['username'] : null;
		$username = preg_replace('#\s+#s', ' ', $username);

		if (mb_strlen($username) < 2) {
			$this->okt['instantMessages']->error(__('c_c_users_error_username_too_short'));
		}
		elseif (mb_strlen($username) > 255) {
			$this->okt['instantMessages']->error(__('c_c_users_error_username_too_long'));
		}
		elseif (mb_strtolower($username) == 'guest') {
			$this->okt['instantMessages']->error(__('c_c_users_error_reserved_username'));
		}

		// don't remenber what the hell this it is...
		/*
		elseif (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $username)
			|| preg_match('/((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))/', $username))
		{
			$this->okt['instantMessages']->error(__('c_c_users_error_reserved_username'));
		}
		elseif ((strpos($username, '[') !== false || strpos($username, ']') !== false) && strpos($username, '\'') !== false && strpos($username, '"') !== false)
		{
			$this->okt['instantMessages']->error(__('c_c_users_error_forbidden_characters'));
		}
		*/

		elseif ($this->userExists($username))
		{
			$dupe = true;

			if (!empty($aParams['id']))
			{
				$user = $this->getUser($aParams['id']);

				if ($user['username'] == $username || $user['email'] == $username) {
					$dupe = false;
				}
			}

			if ($dupe)
			{
				if ($this->okt['config']->users['registration']['merge_username_email']) {
					$this->okt['instantMessages']->error(__('c_c_users_error_email_already_exist'));
				}
				else {
					$this->okt['instantMessages']->error(__('c_c_users_error_username_already_exist'));
				}
			}
		}
	}

	/**
	 * Check user email.
	 *
	 * @param array $aParams
	 * @return void
	 */
	public function checkEmail(array $aParams = [])
	{
		if (empty($aParams['email'])) {
			$this->okt['instantMessages']->error(__('c_c_users_must_enter_email_address'));
		}

		$this->isEmail($aParams['email']);
	}

	/**
	 * Checks if the email is valid.
	 *
	 * @param string $sEmail
	 * @return void
	 */
	public function isEmail($sEmail)
	{
		if (!Utilities::isEmail($sEmail)) {
			$this->okt['instantMessages']->error(sprintf(__('c_c_error_invalid_email'), Escaper::html($sEmail)));
		}
	}

	/**
	 * Checks the password and confirmation password.
	 *
	 * @param array $aParams
	 * @return void
	 */
	public function checkPassword(array $aParams = [])
	{
		if (empty($aParams['password'])) {
			$this->okt['instantMessages']->error(__('c_c_users_must_enter_password'));
		}
		elseif (mb_strlen($aParams['password']) < 4) {
			$this->okt['instantMessages']->error(__('c_c_users_must_enter_password_of_at_least_4_characters'));
		}
		elseif (empty($aParams['password_confirm'])) {
			$this->okt['instantMessages']->error(__('c_c_users_must_confirm_password'));
		}
		elseif ($aParams['password'] != $aParams['password_confirm']) {
			$this->okt['instantMessages']->error(__('c_c_users_error_passwords_do_not_match'));
		}
	}

	/**
	 * Add a user.
	 *
	 * @param array $aParams
	 * @return integer
	 */
	public function addUser(array $aParams = [])
	{
		$this->checkUsername($aParams);

		$this->checkPassword($aParams);

		$this->checkEmail($aParams);

		if ($this->okt['messages']->hasError()) {
			return false;
		}

		if ($this->okt['config']->users['registration']['validation_admin']) {
			$aParams['group_id'] = 0;
		}
		elseif (empty($aParams['group_id']) || !$this->okt['groups']->groupExists($aParams['group_id'])) {
			$aParams['group_id'] = $this->okt['config']->users['registration']['default_group'];
		}

		$sPasswordHash = password_hash($aParams['password'], PASSWORD_DEFAULT);

		if ($this->okt['config']->users['registration']['validation_email'])
		{
			$aParams['activate_string'] = $sPasswordHash;
			$aParams['activate_key'] = Utilities::random_key(8);
		}

		$iTime = time();

		$aAddData = [
			'group_id' 		=> (integer) $aParams['group_id'],
			'civility' 		=> (integer) $aParams['civility'],
			'status' 		=> 0,
			'username' 		=> $aParams['username'],
			'password' 		=> $sPasswordHash,
			'email' 		=> $aParams['email'],
			'registered' 	=> $iTime,
			'last_visit' 	=> $iTime,
		];

		if (!empty($aParams['lastname'])) {
			$aAddData['lastname'] = $aParams['lastname'];
		}

		if (!empty($aParams['firstname'])) {
			$aAddData['firstname'] = $aParams['firstname'];
		}

		if (!empty($aParams['displayname'])) {
			$aAddData['displayname'] = $aParams['displayname'];
		}

		if (!empty($aParams['timezone'])) {
			$aAddData['timezone'] = $aParams['timezone'];
		}

		if (!empty($aParams['language'])) {
			$aAddData['language'] = $aParams['language'];
		}

		if (!empty($aParams['registration_ip'])) {
			$aAddData['registration_ip'] = $aParams['registration_ip'];
		}

		if (!empty($aParams['activate_string'])) {
			$aAddData['activate_string'] = $aParams['activate_string'];
		}

		if (!empty($aParams['activate_key'])) {
			$aAddData['activate_key'] = $aParams['activate_key'];
		}

		$this->okt['db']->insert($this->sUsersTable, $aAddData);

		return $this->okt['db']->lastInsertId();;
	}

	/**
	 * Mise à jour d'une page
	 *
	 * @return boolean
	 */
	public function updUser(array $aParams = [])
	{
		$rsUser = $this->getUsers([
			'id' => $aParams['id']
		]);

		if ($rsUser->isEmpty())
		{
			$this->okt['instantMessages']->error(sprintf(__('c_c_users_error_user_%s_not_exists'), $aParams['id']));
			return false;
		}

		if ($rsUser->group_id == Groups::SUPERADMIN)
		{
			# si on veut désactiver un super-admin alors il faut vérifier qu'il y en as d'autres
			if ($aParams['status'] == 0)
			{
				$iCountSudo = $this->getUsers([
					'group_id' => Groups::SUPERADMIN,
					'status' => 1
				], true);

				if ($iCountSudo < 2)
				{
					$this->okt['instantMessages']->error(__('c_c_users_error_cannot_disable_last_super_administrator'));
					return false;
				}
			}

			# si on veut changer le groupe d'un super-admin alors il faut vérifier qu'il y en as d'autres
			if ($aParams['group_id'] != Groups::SUPERADMIN)
			{
				$iCountSudo = $this->getUsers([
					'group_id' => Groups::SUPERADMIN,
					'status' => 1
				], true);

				if ($iCountSudo < 2)
				{
					$this->okt['instantMessages']->error(__('c_c_users_error_cannot_change_group_last_super_administrator'));
					return false;
				}
			}
		}

		$sql = [];

		$this->checkUsername($aParams);
		$sql[] = 'username=\'' . $this->oDb->escapeStr($aParams['username']) . '\'';

		$this->checkEmail($aParams);
		$sql[] = 'email=\'' . $this->oDb->escapeStr($aParams['email']) . '\'';

		if (isset($aParams['group_id']))
		{
			$sql[] = 'group_id=' . (integer) $aParams['group_id'];
		}

		if (isset($aParams['civility']))
		{
			$sql[] = 'civility=' . (integer) $aParams['civility'];
		}

		if (isset($aParams['status']))
		{
			$sql[] = 'status=' . (integer) $aParams['status'];
		}

		if (isset($aParams['lastname']))
		{
			$sql[] = 'lastname=\'' . $this->oDb->escapeStr($aParams['lastname']) . '\'';
		}

		if (isset($aParams['firstname']))
		{
			$sql[] = 'firstname=\'' . $this->oDb->escapeStr($aParams['firstname']) . '\'';
		}

		if (isset($aParams['displayname']))
		{
			$sql[] = 'displayname=\'' . $this->oDb->escapeStr($aParams['displayname']) . '\'';
		}

		if (isset($aParams['language']))
		{
			$sql[] = 'language=\'' . $this->oDb->escapeStr($aParams['language']) . '\'';
		}

		if (isset($aParams['timezone']))
		{
			$sql[] = 'timezone=\'' . $this->oDb->escapeStr($aParams['timezone']) . '\'';
		}

		if (!$this->oError->isEmpty())
		{
			return false;
		}

		$sQuery = 'UPDATE ' . $this->sUsersTable . ' SET ' . implode(', ', $sql) . ' ' . 'WHERE id=' . (integer) $aParams['id'];

		if (!$this->oDb->execute($sQuery))
		{
			throw new \Exception('Unable to update user in database.');
		}

		return true;
	}

	/**
	 * Modification du mot de passe d'un utilisateur
	 *
	 * @param
	 *        	$aParams
	 * @return boolean
	 */
	public function changeUserPassword(array $aParams = [])
	{
		$this->checkPassword($aParams);

		if (!$this->oError->isEmpty())
		{
			return false;
		}

		$sPasswordHash = password_hash($aParams['password'], PASSWORD_DEFAULT);

		$sQuery = 'UPDATE ' . $this->sUsersTable . ' SET ' . 'password=\'' . $this->oDb->escapeStr($sPasswordHash) . '\' ' . 'WHERE id=' . (integer) $aParams['id'];

		if (!$this->oDb->execute($sQuery))
		{
			throw new \Exception('Unable to update user in database.');
		}

		return true;
	}

	/**
	 * Delete a user.
	 *
	 * @param integer $id
	 * @throws \Exception
	 * @return boolean
	 */
	public function deleteUser($iUserId)
	{
		$rsUser = $this->getUsers([
			'id' => $iUserId
		]);

		if ($rsUser->isEmpty())
		{
			$this->okt['instantMessages']->error(sprintf(__('c_c_users_error_user_%s_not_exists'), $iUserId));
			return false;
		}

		# si on veut supprimer un super-admin alors il faut vérifier qu'il y en as d'autres
		if ($rsUser->group_id == Groups::SUPERADMIN)
		{
			$iCountSudo = $this->getUsers([
				'group_id' => Groups::SUPERADMIN,
				'status' => 1
			], true);

			if ($iCountSudo < 2)
			{
				$this->okt['instantMessages']->error(__('c_c_users_error_cannot_remove_last_super_administrator'));
				return false;
			}
		}

		$sQuery = 'DELETE FROM ' . $this->sUsersTable . ' ' . 'WHERE id=' . (integer) $iUserId;

		if (!$this->oDb->execute($sQuery))
		{
			throw new \Exception('Unable to remove user from database.');
		}

		$this->oDb->optimize($this->sUsersTable);

		# delete user custom fields
		if ($this->okt['config']->users['custom_fields_enabled'])
		{
			$this->fields->delUserValue($iUserId);
		}

		return true;
	}

	/**
	 * Valide un utilisateur (le place dans le groupe par défaut)
	 *
	 * @param integer $iUserId
	 * @return boolean
	 */
	public function validateUser($iUserId)
	{
		if (!$this->userExists($iUserId))
		{
			$this->okt['instantMessages']->error(sprintf(__('c_c_users_error_user_%s_not_exists'), $iUserId));
			return false;
		}

		$sSqlQuery = 'UPDATE ' . $this->sUsersTable . ' SET ' . 'group_id = ' . (integer) $this->okt['config']->users['registration']['default_group'] . ' ' . 'WHERE id=' . (integer) $iUserId;

		if (!$this->oDb->execute($sSqlQuery))
		{
			throw new \Exception('Unable to update user in database.');
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
		if (!$this->userExists($iUserId))
		{
			$this->okt['instantMessages']->error(sprintf(__('c_c_users_error_user_%s_not_exists'), $iUserId));
			return false;
		}

		$sSqlQuery = 'UPDATE ' . $this->sUsersTable . ' SET ' . 'status = 1-status ' . 'WHERE id=' . (integer) $iUserId;

		if (!$this->oDb->execute($sSqlQuery))
		{
			throw new \Exception('Unable to update user in database.');
		}

		return true;
	}

	/**
	 * Définit le statut d'un utilisateur donné
	 *
	 * @param integer $iUserId
	 * @param integer $iStatus
	 * @return boolean
	 */
	public function setUserStatus($iUserId, $iStatus)
	{
		$iStatus = intval($iStatus);

		$rsUser = $this->getUsers([
			'id' => $iUserId
		]);

		if ($rsUser->isEmpty())
		{
			$this->okt['instantMessages']->error(sprintf(__('c_c_users_error_user_%s_not_exists'), $iUserId));
			return false;
		}

		# si on veut désactiver un super-admin alors il faut vérifier qu'il y en as d'autres
		if ($iStatus == 0 && $rsUser->group_id == Groups::SUPERADMIN)
		{
			$iCountSudo = $this->getUsers([
				'group_id' => Groups::SUPERADMIN,
				'status' => 1
			], true);

			if ($iCountSudo < 2)
			{
				$this->okt['instantMessages']->error(__('c_c_users_error_cannot_disable_last_super_administrator'));
				return false;
			}
		}

		$sSqlQuery = 'UPDATE ' . $this->sUsersTable . ' SET ' . 'status = ' . ($iStatus == 1 ? 1 : 0) . ' ' . 'WHERE id=' . (integer) $iUserId;

		if (!$this->oDb->execute($sSqlQuery))
		{
			throw new \Exception('Unable to update user in database.');
		}

		return true;
	}

	/**
	 * Envoi un email avec un nouveau mot de passe.
	 *
	 * @param string $sEmail
	 *        	L'adresse email où envoyer le nouveau mot de passe
	 * @param string $sActivateUrl
	 *        	de la page de validation
	 * @return boolean
	 */
	public function forgetPassword($sEmail, $sActivateUrl)
	{
		# validation de l'adresse fournie
		if (!Utilities::isEmail($sEmail))
		{
			$this->okt['instantMessages']->error(__('c_c_auth_invalid_email'));
			return false;
		}

		# récupération des infos de l'utilisateur
		$rsUser = $this->getUsers([
			'email' => $sEmail
		]);

		if ($rsUser === false || $rsUser->isEmpty())
		{
			$this->okt['instantMessages']->error(__('c_c_auth_unknown_email'));
			return false;
		}

		# génération du nouveau mot de passe et du code d'activation
		$sNewPassword = Utilities::random_key(8, true);
		$sNewPasswordKey = Utilities::random_key(8);

		$sPasswordHash = password_hash($sNewPassword, PASSWORD_DEFAULT);

		$sQuery = 'UPDATE ' . $this->sUsersTable . ' SET ' . 'activate_string=\'' . $this->oDb->escapeStr($sPasswordHash) . '\', ' . 'activate_key=\'' . $this->oDb->escapeStr($sNewPasswordKey) . '\' ' . 'WHERE id=' . (integer) $rsUser->id;

		if (!$this->oDb->execute($sQuery))
		{
			return false;
		}

		# Initialisation du mailer et envoi du mail
		$oMail = new Mailer($this->okt);

		$oMail->setFrom();

		$this->okt['l10n']->loadFile($this->okt['locales_path'] . '/%s/emails', $rsUser->language);

		$aMailParams = [
			'site_title' => $this->okt->page->getSiteTitle($rsUser->language),
			'site_url' => $this->okt['request']->getSchemeAndHttpHost() . $this->okt['config']->app_url,
			'user' => Users::getUserDisplayName($rsUser->username, $rsUser->lastname, $rsUser->firstname, $rsUser->displayname),
			'password' => $sNewPassword,
			'validate_url' => $sActivateUrl . '?uid=' . $rsUser->id . '&key=' . rawurlencode($sNewPasswordKey)
		];

		$oMail->setSubject(__('c_c_emails_request_new_password'));
		$oMail->setBody($this->renderView('emails/newPassword/text', $aMailParams), 'text/plain');

		if ($this->viewExists('emails/newPassword/html'))
		{
			$oMail->addPart($this->renderView('emails/newPassword/html', $aMailParams), 'text/html');
		}

		$oMail->message->setTo($rsUser->email);

		$oMail->send();

		return true;
	}

	/**
	 * Validate a password key for a given user id.
	 *
	 * @param integer $iUserId
	 * @param string $sKey
	 * @return boolean
	 */
	public function validatePasswordKey($iUserId, $sKey)
	{
		$rsUser = $this->getUser($iUserId);

		if ($rsUser === false || $rsUser->isEmpty())
		{
			$this->okt['instantMessages']->error(__('c_c_auth_unknown_email'));
			return false;
		}

		if (rawurldecode($sKey) != $rsUser->activate_key)
		{
			$this->okt['instantMessages']->error(__('c_c_auth_validation_key_not_match'));
			return false;
		}

		$sQuery = 'UPDATE ' . $this->sUsersTable . ' SET ' . 'password=\'' . $rsUser->activate_string . '\', ' . 'activate_string=NULL, ' . 'activate_key=NULL ' . 'WHERE id=' . (integer) $iUserId . ' ';

		if (!$this->oDb->execute($sQuery))
		{
			return false;
		}

		return true;
	}

	/**
	 * Static function that returns user's common name given to his
	 * username, lastname, firstname and displayname.
	 *
	 * @param string $sUsername
	 *        	name
	 * @param string $sLastname
	 *        	last name
	 * @param string $sFirstname
	 *        	first name
	 * @param string $sDisplayName
	 *        	display name
	 * @return string
	 */
	public static function getUserDisplayName($sUsername, $sLastname = null, $sFirstname = null, $sDisplayName = null)
	{
		if (!empty($sDisplayName)) {
			return $sDisplayName;
		}

		if (!empty($sLastname))
		{
			if (!empty($sFirstname)) {
				return $sFirstname . ' ' . $sLastname;
			}

			return $sLastname;
		}
		elseif (!empty($sFirstname)) {
			return $sFirstname;
		}

		return $sUsername;
	}

	/**
	 * Retourne la liste des civilités.
	 *
	 * @param boolean $flip
	 * @return array
	 */
	public static function getCivilities($flip = false)
	{
		$a = [
			1 => __('c_c_user_civility_1'),
			2 => __('c_c_user_civility_2'),
			3 => __('c_c_user_civility_3')
		];

		if ($flip) {
			$a = array_flip($a);
		}

		return $a;
	}
}
