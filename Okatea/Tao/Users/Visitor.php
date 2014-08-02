<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Users;

use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Users\Users;

/**
 * Le gestionnaire d'authentification de l'utilisateur en cours.
 */
class Visitor
{
	/**
	 * Okatea application instance.
	 *
	 * @var Okatea\Tao\Application
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

	/**
	 * Le nom du cookie d'authentification.
	 *
	 * @var string
	 */
	protected $sCookieName;

	/**
	 * Le nom du cookie de redirection après authentification.
	 *
	 * @var string
	 */
	protected $sCookieFromName;

	/**
	 * Le nom du cookie de langue.
	 *
	 * @var string
	 */
	protected $sCookieLangName;

	/**
	 * Le chemin du cookie d'authentification.
	 *
	 * @var string
	 */
	protected $sCookiePath;

	/**
	 * Le domaine du cookie d'authentification.
	 *
	 * @var string
	 */
	protected $sCookieDomain;

	/**
	 * Cookie d'authentification sur protocole sécurisé.
	 *
	 * @var boolean
	 */
	protected $bCookieSecure;

	/**
	 * Durée de la session de visite en secondes (1800 = 30 minutes).
	 *
	 * @var integer
	 */
	protected $iVisitTimeout = 1800;

	/**
	 * Durée d'enregistrement du cookie de session de visite (1209600 = 14 jours).
	 *
	 * @var integer
	 */
	protected $iVisitRememberTime = 1209600;

	/**
	 * Informations utilisateur courant sous forme de recordset.
	 *
	 * @var array
	 */
	public $infos = [];

	/**
	 * Constructeur.
	 *
	 * @param object $okt application instance.
	 * @param string $sCookieName Le nom du cookie d'authentification (otk_auth)
	 * @param string $sCookiePath Le chemin du cookie d'authentification ('/')
	 * @param string $sCookieDomain Le domaine du cookie d'authentification ('')
	 * @param boolean $bCookieSecure Cookie d'authentification sur protocole sécurisé (false)
	 * @return void
	 */
	public function __construct($okt, $sCookieName = 'otk_auth', $sCookieFromName = 'otk_auth_from', $sCookiePath = '/', $sCookieDomain = '', $bCookieSecure = false)
	{
		$this->okt = $okt;

		$this->sUsersTable = $okt['config']->database_prefix . 'core_users';
		$this->sGroupsTable = $okt['config']->database_prefix . 'core_users_groups';
		$this->sGroupsL10nTable = $okt['config']->database_prefix . 'core_users_groups_locales';

		$this->setVisitTimeout($this->okt['config']->user_visit['timeout']);
		$this->setVisitRememberTime($this->okt['config']->user_visit['remember_time']);

		$this->sCookieName = $sCookieName;
		$this->sCookieFromName = $sCookieFromName;
		$this->sCookiePath = $sCookiePath;
		$this->sCookieDomain = $sCookieDomain;
		$this->bCookieSecure = $bCookieSecure;

		$this->authentication();

		$this->initLanguage($this->okt['cookie_language']);
	}

	/**
	 * Retourne une information de l'utilisateur courant.
	 *
	 * @param string $sKey
	 * @return mixed
	 */
	public function __get($sKey)
	{
		if (isset($this->infos[$sKey])) {
			return $this->infos[$sKey];
		}

		return null;
	}

	/**
	 * Change la valeur d'une information de l'utilisateur courant.
	 *
	 * @param string $sKey
	 * @param string $mValue
	 * @return void
	 */
	public function __set($sKey, $mValue)
	{
		return $this->infos[$sKey] = $mValue;
	}

	/**
	 * Retourne toutes les informations d'un utilisateur.
	 * @return array
	 */
	public function getData()
	{
		return $this->infos;
	}

	/**
	 * Authentification de l'utilisateur en cours
	 * et definition de ses informations.
	 *
	 * @return void
	 */
	public function authentication()
	{
		$iTsNow = time();
		$iTsExpire = $iTsNow + $this->iVisitRememberTime;

		# Nous supposons qu'il est un invité
		$aCookie = [
			'user_id' 			=> 1,
			'password_hash' 	=> 'Guest',
			'expiration_time' 	=> 0,
			'expire_hash' 		=> 'Guest'
		];

		# Si un cookie est disponible, on récupère le hash user_id et le mot de passe de lui
		if (isset($_COOKIE[$this->sCookieName]))
		{
			list (
				$aCookie['user_id'],
				$aCookie['password_hash'],
				$aCookie['expiration_time'],
				$aCookie['expire_hash']
			) = explode('|', base64_decode($_COOKIE[$this->sCookieName]));
		}

		# Si c'est un cookie d'un utilisateur connecté il ne devrait pas avoir déjà expiré
		if (intval($aCookie['user_id']) > 1 && intval($aCookie['expiration_time']) > $iTsNow)
		{
			$this->authenticateUser(intval($aCookie['user_id']), $aCookie['password_hash']);

			# Nous validons maintenans le hash du cookie
			if ($aCookie['expire_hash'] !== sha1($this->infos['password'] . intval($aCookie['expiration_time'])))
			{
				$this->setDefaultUser();
			}

			# Si nous sommes retournés à l'utilisateur par défaut, la connexion a échouée
			if ($this->infos['id'] == '1')
			{
				$this->setAuthCookie(base64_encode('1|' . Utilities::random_key(8, false, true) . '|' . $iTsExpire . '|' . Utilities::random_key(8, false, true)), $iTsExpire);
				return;
			}

			# Envoit d'un nouveau cookie mis à jour avec un nouveau timestamp d'expiration
			$iTsExpire = (intval($aCookie['expiration_time']) > $iTsNow + $this->iVisitTimeout) ? $iTsNow + $this->iVisitRememberTime : $iTsNow + $this->iVisitTimeout;
			$this->setAuthCookie(base64_encode($this->infos['id'] . '|' . $this->infos['password'] . '|' . $iTsExpire . '|' . sha1($this->infos['password'] . $iTsExpire)), $iTsExpire);

			$this->infos['logged'] = $iTsNow;

			$this->infos['is_guest'] = false;
			$this->infos['is_admin'] = ($this->infos['group_id'] == Groups::SUPERADMIN || $this->infos['group_id'] == Groups::ADMIN);
			$this->infos['is_superadmin'] = ($this->infos['group_id'] == Groups::SUPERADMIN);
		}
		# sinon l'utilisateur n'est plus connecté
		else {
			$this->setDefaultUser();
		}

		# Store common name
		$this->infos['usedname'] = Users::getUserDisplayName($this->infos['username'], $this->infos['lastname'], $this->infos['firstname'], $this->infos['displayname']);

		# And finally, store perms array
		if (!is_array($this->infos['perms']))
		{
			if (!empty($this->infos['perms'])) {
				$this->infos['perms'] = json_decode($this->infos['perms']);
			}
			else {
				$this->infos['perms'] = [];
			}
		}
	}

	/**
	 * Méthode d'authentification de l'utilisateur courant.
	 *
	 * @param integer $iUserId
	 * @param string $sPasswordHash
	 * @return void
	 */
	protected function authenticateUser($iUserId, $sPasswordHash)
	{
		$sQuery =
			'SELECT u.*, g.* ' .
			'FROM ' . $this->sUsersTable . ' AS u ' .
				'INNER JOIN ' . $this->sGroupsTable . ' AS g ON g.group_id=u.group_id ' .
			'WHERE u.status = 1 AND u.id = :user_id';

		$user = $this->okt['db']->fetchAssoc(
			$sQuery,
			[
				'user_id' => $iUserId
			]
		);

		if ($user === false || $sPasswordHash != $user['password']) {
			$this->setDefaultUser();
		}
		else {
			$this->infos = $user;
		}
	}

	/**
	 * Set default guest user informations.
	 *
	 * @return void
	 */
	public function setDefaultUser()
	{
		$sQuery =
			'SELECT u.*, g.* ' .
			'FROM ' . $this->sUsersTable . ' AS u ' .
				'INNER JOIN ' . $this->sGroupsTable . ' AS g ON g.group_id=u.group_id ' .
			'WHERE u.id = 1';

		$user = $this->okt['db']->fetchAssoc($sQuery);

		if ($user === false) {
			return false;
		}

		$this->infos = $user;

		$this->infos['timezone'] = $this->okt['config']->timezone;
		$this->infos['language'] = $this->okt['config']->language;
		$this->infos['is_guest'] = true;
		$this->infos['is_admin'] = false;
		$this->infos['is_superadmin'] = false;
	}

	/**
	 * Perform login.
	 *
	 * @param string $sUsername
	 * @param string $sPassword
	 * @param boolean $bSavePass
	 * @return boolean
	 */
	public function login($sUsername, $sPassword, $bSavePass = false)
	{
		$sQuery =
			'SELECT id, group_id, password FROM ' .
			$this->sUsersTable .
			' WHERE username = :username';

		$user = $this->okt['db']->fetchAssoc(
			$sQuery,
			[
				'username' => $sUsername
			]
		);

		if ($user === false)
		{
			$this->error->set(__('c_c_auth_unknown_user'));
			return false;
		}

		$sPasswordHash = $user['password'];

		if (!password_verify($sPassword, $sPasswordHash))
		{
			$this->error->set(__('c_c_auth_wrong_password'));
			return false;
		}
		elseif (password_needs_rehash($sPasswordHash, PASSWORD_DEFAULT))
		{
			$sPasswordHash = password_hash($sPassword, PASSWORD_DEFAULT);

			$this->okt['db']->update($this->sUsersTable,
				[
					'password' => $sPasswordHash
				],
				[
					'id' => $user['id']
				]
			);
		}

		if ($user['group_id'] == Groups::UNVERIFIED)
		{
			$this->error->set(__('c_c_auth_account_awaiting_validation'));
			return false;
		}

		$iTsExpire = ($bSavePass) ? time() + $this->iVisitRememberTime : time() + $this->iVisitTimeout;
		$this->setAuthCookie(base64_encode($user['id'] . '|' . $sPasswordHash . '|' . $iTsExpire . '|' . sha1($sPasswordHash . $iTsExpire)), $iTsExpire);

		# log admin
		if (isset($this->okt->logAdmin))
		{
			$this->okt->logAdmin->add([
				'user_id' 	=> $user['id'],
				'username' 	=> $sUsername,
				'code' 		=> 10,
				'message' 	=> __('c_c_log_admin_message_by_form')
			]);
		}

		# -- CORE TRIGGER : userLogin
		$this->okt['triggers']->callTrigger('userLogin');

		return true;
	}

	/**
	 * Perform logout
	 *
	 * @return boolean
	 */
	public function logout()
	{
		# Update last_visit (make sure there's something to update it with)
		if (!empty($this->infos['logged']))
		{
			$this->okt['db']->update(
				$this->sUsersTable,
				[
					'last_visit' => $this->infos['logged']
				],
				[
					'id' => $this->infos['id']
				]
			);
		}

		$this->setAuthCookie('', 0);

		# log admin
		if (isset($this->okt->logAdmin))
		{
			$this->okt->logAdmin->add([
				'user_id' 	=> $this->infos['id'],
				'username' 	=> $this->infos['username'],
				'code' 		=> 11
			]);
		}

		return true;
	}

	/* Language methods
	----------------------------------------------------------*/

	/**
	 * Initialisation de la langue de l'utilisateur.
	 *
	 * @param string $sCookieName
	 * @return void
	 */
	public function initLanguage($sCookieName = 'otk_language')
	{
		$this->sCookieLangName = $sCookieName;

		$this->infos['language'] = $this->getDefaultLang();

		$this->setUserLang($this->infos['language']);
	}

	/**
	 * Change la langue de l'utilisateur.
	 *
	 * @param string $sLanguage
	 * @return void
	 */
	public function setUserLang($sLanguage)
	{
		if ($this->infos['language'] === $sLanguage) {
			return false;
		}

		if (! $this->okt->languages->isActive($sLanguage)) {
			return false;
		}

		$this->infos['language'] = $sLanguage;
		$this->setLangCookie($sLanguage);

		if (! $this->infos['is_guest'])
		{
			$this->okt['db']->update(
				$this->sUsersTable,
				[
					'language' => $sLanguage
				],
				[
					'id' => $this->infos['id']
				]
			);
		}

		return true;
	}

	/**
	 * Retrouve la langue de l'utilisateur.
	 *
	 * @return string
	 */
	protected function getDefaultLang()
	{
		$sLang = null;

		if (isset($_COOKIE[$this->sCookieLangName])) {
			$sLang = $_COOKIE[$this->sCookieLangName];
		}
		else {
			$sLang = $this->okt['request']->getPreferredLanguage();
		}

		if ($this->okt->languages->isActive($sLang)) {
			return $sLang;
		}
		else {
			return $this->okt['config']->language;
		}
	}

	/* Utils
	----------------------------------------------------------*/

	/**
	 * Définit la durée de la session en secondes.
	 *
	 * @param integer $iVisitTimeout
	 * @return void
	 */
	public function setVisitTimeout($iVisitTimeout)
	{
		$this->iVisitTimeout = $iVisitTimeout;
	}

	/**
	 * Définit la durée d'enregistrement du cookie.
	 *
	 * @param integer $iVisitRememberTime
	 * @return void
	 */
	public function setVisitRememberTime($iVisitRememberTime)
	{
		$this->iVisitRememberTime = $iVisitRememberTime;
	}

	/**
	 * Set a cookie
	 *
	 * @param $sValue
	 * @param $iExpire
	 * @return void
	 */
	public function setAuthCookie($sValue, $iExpire)
	{
		setcookie(
			$this->sCookieName,
			$sValue,
			$iExpire,
			$this->sCookiePath,
			$this->sCookieDomain,
			$this->bCookieSecure,
			true
		);
	}

	/**
	 * Set a cookie auth from
	 *
	 * @param
	 *        	$sValue
	 * @return void
	 */
	public function setAuthFromCookie($sValue)
	{
		setcookie(
			$this->sCookieFromName,
			$sValue,
			0,
			$this->sCookiePath,
			$this->sCookieDomain,
			$this->bCookieSecure,
			true
		);
	}

	/**
	 * Set a cookie lang
	 *
	 * @param
	 *        	$sValue
	 * @param
	 *        	$iExpire
	 * @return void
	 */
	public function setLangCookie($sValue, $iExpire = null)
	{
		if ($iExpire === null) {
			$iExpire = time() + $this->iVisitRememberTime;
		}

		setcookie(
			$this->sCookieLangName,
			$sValue,
			$iExpire,
			$this->sCookiePath,
			$this->sCookieDomain,
			$this->bCookieSecure,
			true
		);
	}
}
