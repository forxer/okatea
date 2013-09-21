<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @class oktAuth
 * @ingroup okt_classes_core
 * @brief Le gestionnaire d'authentification de l'utilisateur en cours.
 *
 */
class oktAuth
{
	/**
	 * Durée de la session en secondes.
	 */
	const timeout_visit = 1800;

	/**
	 * Durée d'enregistrement du cookie.
	 *
	 * 1209600 = 14 jours
	 */
	const remember_time = 1209600;

	/**
	 * Identifiant groupe utilisateurs non-vérifiés.
	 */
	const unverified_group_id = 0;

	/**
	 * Identifiant groupe utilisateurs super-admin.
	 */
	const superadmin_group_id = 1;

	/**
	 * Identifiant groupe utilisateurs admin.
	 */
	const admin_group_id = 2;

	/**
	 * Identifiant groupe utilisateurs invités.
	 */
	const guest_group_id = 3;

	/**
	 * Identifiant groupe utilisateurs membres.
	 */
	const member_group_id = 4;

	/**
	 * L'objet core.
	 * @var object oktCore
	 */
	protected $okt;

	/**
	 * L'objet gestionnaire de base de données.
	 * @var object
	 */
	protected $oDb;

	/**
	 * L'objet gestionnaire d'erreurs.
	 * @var object
	 */
	protected $oError;

	/**
	 * Le nom de la table users.
	 * @var string
	 */
	protected $t_users;

	/**
	 * Le nom de la table groups.
	 * @var string
	 */
	protected $t_groups;

	/**
	 * Le nom de la table online.
	 * @var string
	 */
	protected $t_online;

	/**
	 * Le nom du cookie d'authentification.
	 * @var string
	 */
	protected $sCookieName;

	/**
	 * Le nom du cookie de redirection après authentification.
	 * @var string
	 */
	protected $sCookieFromName;

	/**
	 * Le nom du cookie de langue.
	 * @var string
	 */
	protected $sCookieLangName;

	/**
	 * Le chemin du cookie d'authentification.
	 * @var string
	 */
	protected $sCookiePath;

	/**
	 * Le domaine du cookie d'authentification.
	 * @var string
	 */
	protected $sCookieDomain;

	/**
	 * Cookie d'authentification sur protocole sécurisé.
	 * @var boolean
	 */
	protected $bCookieSecure;

	/**
	 * Informations utilisateur courant sous forme de recordset.
	 * @var object recordset
	 */
	public $infos = null;

	/**
	 * Constructeur.
	 *
	 * @param object $okt				Instance de l'objet oktCore
	 * @param string $sCookieName 		Le nom du cookie d'authentification (otk_auth)
	 * @param string $sCookiePath 		Le chemin du cookie d'authentification ('/')
	 * @param string $sCookieDomain 	Le domaine du cookie d'authentification ('')
	 * @param boolean $bCookieSecure 	Cookie d'authentification sur protocole sécurisé (false)
	 * @return void
	 */
	public function __construct($okt, $sCookieName='otk_auth', $sCookieFromName='otk_auth_from', $sCookiePath='/', $sCookieDomain='', $bCookieSecure=false)
	{
		$this->okt = $okt;
		$this->oDb = $okt->db;
		$this->oError = $okt->error;

		$this->t_users = $this->oDb->prefix.'core_users';
		$this->t_groups = $this->oDb->prefix.'core_users_groups';
		$this->t_online = $this->oDb->prefix.'core_users_online';

		$this->sCookieName = $sCookieName;
		$this->sCookieFromName = $sCookieFromName;
		$this->sCookiePath = $sCookiePath;
		$this->sCookieDomain = $sCookieDomain;
		$this->bCookieSecure = $bCookieSecure;
	}

	/**
	 * Retourne une information de l'utilisateur courant.
	 *
	 * @param string $sKey
	 * @return mixed
	 */
	public function __get($sKey)
	{
		return $this->infos->f($sKey);
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
		return $this->infos->setField($sKey, $mValue);
	}

	/**
	 * Retourne toutes les informations d'un utilisateur.
	 *
	 * @param $id
	 * @return array
	 */
	public function getData($id=null)
	{
		return $this->infos->getData($id);
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
		$iTsExpire = $iTsNow + self::remember_time;

		# Nous supposons qu'il est un invité
		$aCookie = array(
			'user_id' => 1,
			'password_hash' => 'Guest',
			'expiration_time' => 0,
			'expire_hash' => 'Guest'
		);

		# Si un cookie est disponible, on récupère le hash user_id et le mot de passe de lui
		if (isset($_COOKIE[$this->sCookieName])) {
			list($aCookie['user_id'], $aCookie['password_hash'], $aCookie['expiration_time'], $aCookie['expire_hash']) = explode('|', base64_decode($_COOKIE[$this->sCookieName]));
		}

		# Si c'est un cookie d'un utilisateur connecté il ne devrait pas avoir déjà expiré
		if (intval($aCookie['user_id']) > 1 && intval($aCookie['expiration_time']) > $iTsNow)
		{
			$this->authenticateUser(intval($aCookie['user_id']), $aCookie['password_hash']);

			# Nous validons maintenans le hash du cookie
			if ($aCookie['expire_hash'] !== sha1($this->infos->f('salt').$this->infos->f('password').util::hash(intval($aCookie['expiration_time']), $this->infos->f('salt')))) {
				$this->setDefaultUser();
			}

			# Si nous sommes retournés à l'utilisateur par défaut, la connexion a échouée
			if ($this->infos->f('id') == '1')
			{
				$this->setAuthCookie(base64_encode('1|'.util::random_key(8, false, true).'|'.$iTsExpire.'|'.util::random_key(8, false, true)), $iTsExpire);
				return;
			}

			# Envoit d'un nouveau cookie mis à jour avec un nouveau timestamp d'expiration
			$iTsExpire = (intval($aCookie['expiration_time']) > $iTsNow + self::timeout_visit) ? $iTsNow + self::remember_time : $iTsNow + self::timeout_visit;
			$this->setAuthCookie(base64_encode($this->infos->f('id').'|'.$this->infos->f('password').'|'.$iTsExpire.'|'.sha1($this->infos->f('salt').$this->infos->f('password').util::hash($iTsExpire, $this->infos->f('salt')))), $iTsExpire);

			# Mise à jour de la liste des utilisateurs en ligne
			if ($this->infos->f('logged') == '')
			{
				$this->infos->set('logged', $iTsNow);
				$this->infos->set('csrf_token', util::random_key(40, false, true));
				$this->infos->set('prev_url', $this->get_current_url(255));

				$sSqlQuery =
				'REPLACE INTO '.$this->t_online.' (user_id, ident, logged, csrf_token, prev_url) '.
				'VALUES ('.
					$this->infos->f('id').', '.
					'\''.$this->oDb->escapeStr($this->infos->f('username')).'\', '.
					$this->infos->f('logged').', '.
					'\''.$this->infos->f('csrf_token').'\', '.
					(($this->infos->f('prev_url') != null) ? '\''.$this->oDb->escapeStr($this->infos->f('prev_url')).'\'' : 'NULL').
				')';

				if (!$this->oDb->execute($sSqlQuery)) {
					return false;
				}
			}
			else
			{
				# Cas particulier: session expirée mais aucun autre utilisateur a navigué sur le site
				if ($this->infos->f('logged') < ($iTsNow-self::timeout_visit))
				{
					$sSqlQuery =
					'UPDATE '.$this->t_users.' SET '.
						'last_visit='.$this->infos->f('logged').' '.
					'WHERE id='.$this->infos->f('id');

					if (!$this->oDb->execute($sSqlQuery)) {
						return false;
					}

					$this->infos->set('last_visit', $this->infos->f('logged'));
				}

				# Maintenant mise à jour du moment de la connexion
				$sSqlQuery =
				'UPDATE '.$this->t_online.' SET '.
					'logged='.$iTsNow.', '.
					'prev_url=\''.$this->oDb->escapeStr($this->get_current_url(255)).'\' '.
					($this->infos->f('idle') == '1' ? ', idle=0' : '').
				'WHERE user_id='.$this->infos->f('id');

				if (!$this->oDb->execute($sSqlQuery)) {
					return false;
				}
			}

			$this->infos->set('is_guest', false);
			$this->infos->set('is_admin',($this->infos->f('group_id') == self::superadmin_group_id || $this->infos->f('group_id') == self::admin_group_id));
			$this->infos->set('is_superadmin',($this->infos->f('group_id') == self::superadmin_group_id));
		}
		# sinon l'utilisateur n'est plus connecté
		else {
			$this->setDefaultUser();
		}

		# And finally, store perms array
		if ($this->infos->f('perms') != '' && !is_array($this->infos->perms)) {
			$this->infos->set('perms',unserialize($this->infos->perms));
		}
		elseif (!is_array($this->infos->f('perms'))) {
			$this->infos->set('perms',array());
		}
	}

	/**
	 * Méthode d'authentification de l'utilisateur courant.
	 *
	 * @param $mUser
	 * @param $sPasswordHash
	 * @return void
	 */
	public function authenticateUser($mUser, $sPasswordHash)
	{
		$sSqlQuery =
		'SELECT u.*, g.*, o.logged, o.idle, o.csrf_token, o.prev_url '.
		'FROM '.$this->t_users.' AS u '.
			'INNER JOIN '.$this->t_groups.' AS g ON g.group_id=u.group_id '.
			'LEFT JOIN '.$this->t_online.' AS o ON o.user_id=u.id '.
		'WHERE u.active = 1 AND ';

		if (util::isInt($mUser)) {
			$sSqlQuery .= 'u.id='.(integer)$mUser.' ';
		}
		else {
			$sSqlQuery .= 'u.username=\''.$this->oDb->escapeStr($mUser).'\' ';
		}

		if (($rs = $this->oDb->select($sSqlQuery)) === false) {
			return false;
		}

		if ($rs->isEmpty() || ($sPasswordHash != $rs->f('password'))) {
			$this->setDefaultUser();
		}
		else {
			$this->infos = $rs;
		}
	}

	/**
	 * Set default user informations.
	 *
	 * @return void
	 */
	public function setDefaultUser()
	{
		$sRemoteAddr = http::realIP();

		# Fetch guest user
		$sSqlQuery =
		'SELECT u.*, g.*, o.logged, o.csrf_token, o.prev_url '.
		'FROM '.$this->t_users.' AS u '.
			'INNER JOIN '.$this->t_groups.' AS g ON g.group_id=u.group_id '.
			'LEFT JOIN '.$this->t_online.' AS o ON o.ident=\''.$this->oDb->escapeStr($sRemoteAddr).'\' '.
		'WHERE u.id=1';

		if (($rs = $this->oDb->select($sSqlQuery)) === false) {
			return false;
		}

		if ($rs->isEmpty()) {
			return false;
		}
		else {
			$this->infos = $rs;
		}

		# Update online list
		if ($this->infos->f('logged') == '')
		{
			$this->infos->set('logged', time());
			$this->infos->set('csrf_token', util::random_key(40, false, true));
			$this->infos->set('prev_url', $this->get_current_url(255));

			# REPLACE INTO avoids a user having two rows in the online table
			$sSqlQuery =
			'REPLACE INTO '.$this->t_online.' (user_id, ident, logged, csrf_token, prev_url) '.
			'VALUES ('.
				'1,'.
				'\''.$this->oDb->escapeStr($sRemoteAddr).'\', '.
				$this->infos->f('logged').', '.
				'\''.$this->infos->f('csrf_token').'\', '.
				(($this->infos->f('prev_url') !== null) ? '\''.$this->oDb->escapeStr($this->infos->f('prev_url')).'\'' : 'NULL').
			')';

			if (!$this->oDb->execute($sSqlQuery)) {
				return false;
			}
		}
		else 
		{
			$sSqlQuery =
			'UPDATE '.$this->t_online.' SET '.
				'logged='.time().', '.
				'prev_url=\''.$this->oDb->escapeStr($this->get_current_url(255)).'\' '.
			'WHERE ident=\''.$this->oDb->escapeStr($sRemoteAddr).'\'';

			if (!$this->oDb->execute($sSqlQuery)) {
				return false;
			}
		}

		$this->infos->set('timezone', $this->okt->config->timezone);
		$this->infos->set('language', $this->okt->config->language);
		$this->infos->set('is_guest', true);
		$this->infos->set('is_admin', false);
		$this->infos->set('is_superadmin', false);
	}

	/**
	 * Perform login.
	 *
	 * @param string $sUsername
	 * @param string $sPassword
	 * @param bollean $save_pass
	 * @return boolean
	 */
	public function login($sUsername, $sPassword, $save_pass=false)
	{
		$sSqlQuery =
		'SELECT id, group_id, password, salt '.
			'FROM '.$this->t_users.' '.
		'WHERE username=\''.$this->oDb->escapeStr($sUsername).'\' ';

		if (($rs = $this->oDb->select($sSqlQuery)) === false) {
			return false;
		}

		if ($rs->isEmpty()) {
			$this->oError->set(__('c_c_auth_unknown_user'));
			return false;
		}

		$sPasswordHash = $rs->password;

		if (!password::verify($sPassword, $sPasswordHash))
		{
			$this->oError->set(__('c_c_auth_wrong_password'));
			return false;
		}
		else if (password::needs_rehash($sPasswordHash, PASSWORD_DEFAULT))
		{
			$sPasswordHash = password::hash($sPassword, PASSWORD_DEFAULT);

			$sSqlQuery =
			'UPDATE '.$this->t_users.' SET '.
				'password=\''.$this->oDb->escapeStr($sPasswordHash).'\' '.
			'WHERE id='.$rs->id;

			if (!$this->oDb->execute($sSqlQuery)) {
				return false;
			}
		}

		# Update the status if this is the first time the user logged in
		if ($rs->group_id == self::unverified_group_id)
		{
			/*
			$sSqlQuery =
			'UPDATE '.$this->t_users.' SET '.
				'group_id='.self::member_group_id.' '.
			'WHERE id='.$rs->id;

			if (!$this->oDb->execute($sSqlQuery)) {
				return false;
			}
			*/

			$this->oError->set(__('c_c_auth_account_awaiting_validation'));
			return false;
		}

		# Remove this user's guest entry from the online list
		$sSqlQuery =
		'DELETE FROM '.$this->t_online.' '.
		'WHERE ident=\''.$this->oDb->escapeStr(http::realIP()).'\'';

		if (!$this->oDb->execute($sSqlQuery)) {
			return false;
		}

		$iTsExpire = ($save_pass) ? time() + self::remember_time : time() + self::timeout_visit;
		$this->setAuthCookie(base64_encode($rs->id.'|'.$sPasswordHash.'|'.$iTsExpire.'|'.sha1($rs->salt.$sPasswordHash.util::hash($iTsExpire, $rs->salt))), $iTsExpire);

		# log admin
		if (isset($this->okt->logAdmin))
		{
			$this->okt->logAdmin->add(array(
				'user_id' => $rs->id,
				'username' => $sUsername,
				'code' => 10,
				'message' => __('c_c_log_admin_message_by_form')
			));
		}

		# -- CORE TRIGGER : userLogin
		$this->okt->triggers->callTrigger('userLogin', $this->okt, $rs);

		return true;
	}

	/**
	 * Perform logout
	 *
	 * @return boolean
	 */
	public function logout()
	{
		$sSqlQuery =
		'DELETE FROM '.$this->t_online.' '.
		'WHERE user_id='.$this->infos->f('id');

		if (!$this->oDb->execute($sSqlQuery)) {
			return false;
		}

		# Update last_visit (make sure there's something to update it with)
		if ($this->infos->f('logged') != '')
		{
			$sSqlQuery =
			'UPDATE '.$this->t_users.' SET '.
			'last_visit='.$this->infos->f('logged').' '.
			'WHERE id='.$this->infos->f('id');

			if (!$this->oDb->execute($sSqlQuery)) {
				return false;
			}
		}

		$this->setAuthCookie('', 0);

		# log admin
		if (isset($this->okt->logAdmin))
		{
			$this->okt->logAdmin->add(array(
				'user_id' => $this->infos->f('id'),
				'username' => $this->infos->f('username'),
				'code' => 11
			));
		}

		return true;
	}

	/**
	 * Envoi un email avec un nouveau mot de passe.
	 *
	 * @param string $sEmail    		L'adresse email où envoyer le nouveau mot de passe
	 * @param string $sActivateUrl		L'URL de la page de validation
	 * @return boolean
	 */
	public function forgetPassword($sEmail, $sActivateUrl)
	{
		$sEmail = strtolower(trim($sEmail));

		# validation de l'adresse fournie
		if (!text::isEmail($sEmail))
		{
			$this->oError->set(__('c_c_auth_invalid_email'));
			return false;
		}

		# récupération des infos de l'utilisateur
		$sSqlQuery =
		'SELECT id, username, lastname, firstname, salt '.
		'FROM '.$this->t_users.' '.
		'WHERE email=\''.$this->oDb->escapeStr($sEmail).'\'';

		if (($rs = $this->oDb->select($sSqlQuery)) === false) {
			return false;
		}

		if ($rs->isEmpty()) {
			$this->oError->set(__('c_c_auth_unknown_email'));
			return false;
		}

		while ($rs->fetch())
		{
			# génération du nouveau mot de passe et du code d'activation
			$sNewPassword = util::random_key(8,true);
			$sNewPasswordKey = util::random_key(8);

			$sPasswordHash = password::hash($sNewPassword, PASSWORD_DEFAULT);

			$sSqlQuery =
			'UPDATE '.$this->t_users.' SET '.
				'activate_string=\''.$sPasswordHash.'\', '.
				'activate_key=\''.$sNewPasswordKey.'\' '.
			'WHERE id='.(integer)$rs->id;

			if (!$this->oDb->execute($sSqlQuery)) {
				return false;
			}

			# Initialisation du mailer et envoi du mail
			$oMail = new oktMail($this->okt);
			$oMail->setFrom();
			$oMail->message->setTo($sEmail);

			$oMail->useFile(OKT_LOCALES_PATH.'/'.$this->okt->user->language.'/templates/activate_password.tpl', array(
				'SITE_TITLE' => util::getSiteTitle(),
				'SITE_URL' => $this->okt->config->app_url,
				'USERNAME' => self::getUserCN($rs->username, $rs->lastname, $rs->firstname),
				'NEW_PASSWORD' => $sNewPassword,
				'ACTIVATION_URL' => $sActivateUrl.'?action=validate_password&uid='.$rs->id.'&key='.rawurlencode($sNewPasswordKey),
			));

			$oMail->send();
		}

		return true;
	}


	public function validatePasswordKey($iUserId, $sKey)
	{
		# récupération des infos de l'utilisateur
		$sSqlQuery =
		'SELECT activate_string, activate_key '.
		'FROM '.$this->t_users.' '.
		'WHERE id='.(integer)$iUserId.' ';

		if (($rs = $this->oDb->select($sSqlQuery)) === false) {
			return false;
		}

		if ($rs->isEmpty()) {
			$this->oError->set(__('c_c_auth_unknown_user'));
			return false;
		}

		if ($sKey != $rs->activate_key) {
			$this->oError->set(__('c_c_auth_validation_key_not_match'));
			return false;
		}

		$sSqlQuery =
		'UPDATE '.$this->t_users.' SET '.
			'password=\''.$rs->activate_string.'\', '.
			'activate_string=NULL, '.
			'activate_key=NULL '.
		'WHERE id='.(integer)$iUserId.' ';

		if (!$this->oDb->execute($sSqlQuery)) {
			return false;
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
	public function initLanguage($sCookieName='otk_language')
	{
		$this->sCookieLangName = $sCookieName;

		$this->infos->set('language', $this->getDefaultLang());

		$this->setUserLang($this->infos->f('language'));
	}

	/**
	 * Change la langue de l'utilisateur.
	 *
	 * @param string $sLanguage
	 * @return void
	 */
	public function setUserLang($sLanguage)
	{
		if ($this->infos->f('language') === $sLanguage) {
			return false;
		}

		if (!$this->okt->languages->isActive($sLanguage)) {
			return false;
		}

		$this->infos->set('language', $sLanguage);
		$this->setLangCookie($sLanguage);

		if (!$this->infos->f('is_guest'))
		{
			$sSqlQuery =
			'UPDATE '.$this->t_users.' SET '.
				'language=\''.$this->oDb->escapeStr($sLanguage).'\' '.
			'WHERE id='.(integer)$this->infos->f('id');

			if (!$this->oDb->execute($sSqlQuery)) {
				return false;
			}
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
		$sLang = '';

		if (isset($_COOKIE[$this->sCookieLangName])) {
			$sLang = $_COOKIE[$this->sCookieLangName];
		}
		elseif (($acceptLanguage = http::getAcceptLanguage()) != '') {
			$sLang = $acceptLanguage;
		}

		if ($this->okt->languages->isActive($sLang)) {
			return $sLang;
		}
		else {
			return $this->okt->config->language;
		}
	}


	/* Utils
	----------------------------------------------------------*/

	/**
	 * Set a cookie
	 *
	 * @param $sValue
	 * @param $iExpire
	 * @return void
	 */
	public function setAuthCookie($sValue, $iExpire)
	{
		setcookie($this->sCookieName, $sValue, $iExpire, $this->sCookiePath, $this->sCookieDomain, $this->bCookieSecure, true);
	}

	/**
	 * Set a cookie auth from
	 *
	 * @param $sValue
	 * @return void
	 */
	public function setAuthFromCookie($sValue)
	{
		setcookie($this->sCookieFromName, $sValue, 0, $this->sCookiePath, $this->sCookieDomain, $this->bCookieSecure, true);
	}

	/**
	 * Set a cookie lang
	 *
	 * @param $sValue
	 * @param $iExpire
	 * @return void
	 */
	public function setLangCookie($sValue, $iExpire=null)
	{
		if ($iExpire === null) {
			$iExpire = time() + self::remember_time;
		}

		setcookie($this->sCookieLangName, $sValue, $iExpire, $this->sCookiePath, $this->sCookieDomain, $this->bCookieSecure, true);
	}

	/**
	 * Static function that returns user's common name given to his
	 * <var>username</var>, <var>lastname</var>, <var>firstname</var> and
	 * <var>displayname</var>.
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

	public function get_current_url($iMaxLength=0)
	{
		$sUrl = $this->okt->config->self_uri;

		if ($iMaxLength == 0 || strlen($sUrl) <= $iMaxLength) {
			return $sUrl;
		}

		return null;
	}


} # class oktAuth
