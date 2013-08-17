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
	 * Durée de la session en secondes
	 */
	const timeout_visit = 1800;

	/**
	 * Durée d'enregistrement du cookie
	 *
	 * 1209600 = 14 jours
	 */
	const remember_time = 1209600;

	/**
	 * Identifiant groupe utilisateurs non-vérifiés
	 */
	const unverified_group_id = 0;

	/**
	 * Identifiant groupe utilisateurs super-admin
	 */
	const superadmin_group_id = 1;

	/**
	 * Identifiant groupe utilisateurs admin
	 */
	const admin_group_id = 2;

	/**
	 * Identifiant groupe utilisateurs invités
	 */
	const guest_group_id = 3;

	/**
	 * Identifiant groupe utilisateurs membres
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
	protected $db;

	/**
	 * L'objet gestionnaire d'erreurs
	 * @var object
	 */
	protected $error;

	/**
	 * Le nom de la table users
	 * @var string
	 */
	protected $t_users;

	/**
	 * Le nom de la table groups
	 * @var string
	 */
	protected $t_groups;

	/**
	 * Le nom de la table online
	 * @var string
	 */
	protected $t_online;

	/**
	 * Le nom du cookie d'authentification
	 * @var string
	 */
	protected $cookie_name;

	/**
	 * Le nom du cookie de redirection après authentification
	 * @var string
	 */
	protected $cookie_from_name;

	/**
	 * Le nom du cookie de langue
	 * @var string
	 */
	protected $cookie_lang_name;

	/**
	 * Le chemin du cookie d'authentification
	 * @var string
	 */
	protected $cookie_path;

	/**
	 * Le domaine du cookie d'authentification
	 * @var string
	 */
	protected $cookie_domain;

	/**
	 * Cookie d'authentification sur protocole sécurisé
	 * @var boolean
	 */
	protected $cookie_secure;

	/**
	 * Informations utilisateur courant sous forme de recordset
	 * @var object recordset
	 */
	public $infos = null;

	/**
	 * Constructeur.
	 *
	 * @param object $okt				Instance de l'objet oktCore
	 * @param string $cookie_name 		Le nom du cookie d'authentification (otk_auth)
	 * @param string $cookie_path 		Le chemin du cookie d'authentification ('/')
	 * @param string $cookie_domain 	Le domaine du cookie d'authentification ('')
	 * @param boolean $cookie_secure 	Cookie d'authentification sur protocole sécurisé (false)
	 * @return void
	 */
	public function __construct($okt, $cookie_name='otk_auth', $cookie_from_name='otk_auth_from', $cookie_path='/', $cookie_domain='', $cookie_secure=false)
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;

		$this->t_users = $this->db->prefix.'core_users';
		$this->t_groups = $this->db->prefix.'core_users_groups';
		$this->t_online = $this->db->prefix.'core_users_online';

		$this->cookie_name = $cookie_name;
		$this->cookie_from_name = $cookie_from_name;
		$this->cookie_path = $cookie_path;
		$this->cookie_domain = $cookie_domain;
		$this->cookie_secure = $cookie_secure;
	}

	/**
	 * Retourne une information de l'utilisateur courant
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->infos->f($key);
	}

	/**
	 * Change la valeur d'une information de l'utilisateur courant
	 *
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		return $this->infos->setField($key, $value);
	}

	/**
	 * Retourne toutes les informations d'un utilisateur
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
	 * et definition de ses informations
	 *
	 * @return void
	 */
	public function authentication()
	{
		$iTsNow = time();
		$iTsExpire = $iTsNow + self::remember_time;

		# Nous supposons qu'il est un invité
		$cookie = array(
			'user_id' => 1,
			'password_hash' => 'Guest',
			'expiration_time' => 0,
			'expire_hash' => 'Guest'
		);

		# Si un cookie est disponible, on récupère le hash user_id et le mot de passe de lui
		if (isset($_COOKIE[$this->cookie_name])) {
			list($cookie['user_id'],$cookie['password_hash'],$cookie['expiration_time'],$cookie['expire_hash']) = explode('|', base64_decode($_COOKIE[$this->cookie_name]));
		}

		# Si c'est un cookie d'un utilisateur connecté il ne devrait pas avoir déjà expiré
		if (intval($cookie['user_id']) > 1 && intval($cookie['expiration_time']) > $iTsNow)
		{
			$this->authenticateUser(intval($cookie['user_id']), $cookie['password_hash']);

			# Nous validons maintenans le hash du cookie
			if ($cookie['expire_hash'] !== sha1($this->infos->f('salt').$this->infos->f('password').util::hash(intval($cookie['expiration_time']), $this->infos->f('salt')))) {
				$this->setDefaultUser();
			}

			# Si nous sommes retournés à l'utilisateur par défaut, la connexion a échouée
			if ($this->infos->f('id') == '1')
			{
				$this->setAuthCookie(base64_encode('1|'.util::random_key(8, false, true).'|'.$iTsExpire.'|'.util::random_key(8, false, true)), $iTsExpire);
				return;
			}

			# Envoit d'un nouveau cookie mis à jour avec un nouveau timestamp d'expiration
			$iTsExpire = (intval($cookie['expiration_time']) > $iTsNow + self::timeout_visit) ? $iTsNow + self::remember_time : $iTsNow + self::timeout_visit;
			$this->setAuthCookie(base64_encode($this->infos->f('id').'|'.$this->infos->f('password').'|'.$iTsExpire.'|'.sha1($this->infos->f('salt').$this->infos->f('password').util::hash($iTsExpire, $this->infos->f('salt')))), $iTsExpire);

			# Mise à jour de la liste des utilisateurs en ligne
			if ($this->infos->f('logged') == '')
			{
				$this->infos->set('logged',$iTsNow);
				$this->infos->set('csrf_token', util::random_key(40, false, true));
				$this->infos->set('prev_url',$this->get_current_url(255));

				$query =
				'REPLACE INTO '.$this->t_online.' (user_id, ident, logged, csrf_token, prev_url) '.
				'VALUES ('.
					$this->infos->f('id').', '.
					'\''.$this->db->escapeStr($this->infos->f('username')).'\', '.
					$this->infos->f('logged').', '.
					'\''.$this->infos->f('csrf_token').'\', '.
					(($this->infos->f('prev_url') != null) ? '\''.$this->db->escapeStr($this->infos->f('prev_url')).'\'' : 'NULL').
				')';

				if (!$this->db->execute($query)) {
					return false;
				}
			}
			else
			{
				# Cas particulier: session expirée mais aucun autre utilisateur a navigué sur le site
				if ($this->infos->f('logged') < ($iTsNow-self::timeout_visit))
				{
					$query =
					'UPDATE '.$this->t_users.' SET '.
						'last_visit='.$this->infos->f('logged').' '.
					'WHERE id='.$this->infos->f('id');

					if (!$this->db->execute($query)) {
						return false;
					}

					$this->infos->set('last_visit',$this->infos->f('logged'));
				}

				# Maintenant mise à jour du moment de la connexion
				$query =
				'UPDATE '.$this->t_online.' SET '.
					'logged='.$iTsNow.', '.
					'prev_url=\''.$this->db->escapeStr($this->get_current_url(255)).'\' '.
					($this->infos->f('idle') == '1' ? ', idle=0' : '').
				'WHERE user_id='.$this->infos->f('id');

				if (!$this->db->execute($query)) {
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
	 * Méthode d'authentification de l'utilisateur courant
	 *
	 * @param $user
	 * @param $password_hash
	 * @return void
	 */
	public function authenticateUser($user, $password_hash)
	{
		$query =
		'SELECT u.*, g.*, o.logged, o.idle, o.csrf_token, o.prev_url '.
		'FROM '.$this->t_users.' AS u '.
			'INNER JOIN '.$this->t_groups.' AS g ON g.group_id=u.group_id '.
			'LEFT JOIN '.$this->t_online.' AS o ON o.user_id=u.id '.
		'WHERE u.active = 1 AND ';

		if (util::isInt($user)) {
			$query .= 'u.id='.(integer)$user.' ';
		}
		else {
			$query .= 'u.username=\''.$this->db->escapeStr($user).'\' ';
		}

		if (($rs = $this->db->select($query)) === false) {
			return false;
		}

		if ($rs->isEmpty() || ($password_hash != $rs->f('password'))) {
			$this->setDefaultUser();
		}
		else {
			$this->infos = $rs;
		}
	}

	/**
	 * Set default user informations
	 *
	 * @return void
	 */
	public function setDefaultUser()
	{
		$remote_addr = http::realIP();

		# Fetch guest user
		$query =
		'SELECT u.*, g.*, o.logged, o.csrf_token, o.prev_url '.
		'FROM '.$this->t_users.' AS u '.
			'INNER JOIN '.$this->t_groups.' AS g ON g.group_id=u.group_id '.
			'LEFT JOIN '.$this->t_online.' AS o ON o.ident=\''.$this->db->escapeStr($remote_addr).'\' '.
		'WHERE u.id=1';

		if (($rs = $this->db->select($query)) === false) {
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
			$query =
			'REPLACE INTO '.$this->t_online.' (user_id, ident, logged, csrf_token, prev_url) '.
			'VALUES ('.
				'1,'.
				'\''.$this->db->escapeStr($remote_addr).'\', '.
				$this->infos->f('logged').', '.
				'\''.$this->infos->f('csrf_token').'\', '.
				(($this->infos->f('prev_url') !== null) ? '\''.$this->db->escapeStr($this->infos->f('prev_url')).'\'' : 'NULL').
			')';

			if (!$this->db->execute($query)) {
				return false;
			}
		}
		else {
			$query =
			'UPDATE '.$this->t_online.' SET '.
				'logged='.time().', '.
				'prev_url=\''.$this->db->escapeStr($this->get_current_url(255)).'\' '.
			'WHERE ident=\''.$this->db->escapeStr($remote_addr).'\'';

			if (!$this->db->execute($query)) {
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
	 * Perform login
	 *
	 * @param string $username
	 * @param string $password
	 * @param bollean $save_pass
	 * @return boolean
	 */
	public function login($username, $password, $save_pass=false)
	{
		$query =
		'SELECT id, group_id, password, salt '.
			'FROM '.$this->t_users.' '.
		'WHERE username=\''.$this->db->escapeStr($username).'\' ';

		if (($rs = $this->db->select($query)) === false) {
			return false;
		}

		if ($rs->isEmpty()) {
			$this->error->set('Utilisateur inconnu.');
			return false;
		}

		$password_hash = $rs->password;

		if (!password::verify($password, $password_hash))
		{
			$this->error->set('Mauvais mot de passe.');
			return false;
		}
		else if (password::needs_rehash($password_hash, PASSWORD_DEFAULT))
		{
			$password_hash = password::hash($password, PASSWORD_DEFAULT);

			$query =
			'UPDATE '.$this->t_users.' SET '.
				'password=\''.$this->db->escapeStr($password_hash).'\' '.
			'WHERE id='.$rs->id;

			if (!$this->db->execute($query)) {
				return false;
			}
		}

		# Update the status if this is the first time the user logged in
		if ($rs->group_id == self::unverified_group_id)
		{
			/*
			$query =
			'UPDATE '.$this->t_users.' SET '.
				'group_id='.self::member_group_id.' '.
			'WHERE id='.$rs->id;

			if (!$this->db->execute($query)) {
				return false;
			}
			*/

			$this->error->set('Votre compte est en attente de validation.');
			return false;
		}

		# Remove this user's guest entry from the online list
		$query =
		'DELETE FROM '.$this->t_online.' '.
		'WHERE ident=\''.$this->db->escapeStr(http::realIP()).'\'';

		if (!$this->db->execute($query)) {
			return false;
		}

		$iTsExpire = ($save_pass) ? time() + self::remember_time : time() + self::timeout_visit;
		$this->setAuthCookie(base64_encode($rs->id.'|'.$password_hash.'|'.$iTsExpire.'|'.sha1($rs->salt.$password_hash.util::hash($iTsExpire, $rs->salt))), $iTsExpire);

		# log admin
		if (isset($this->okt->logAdmin))
		{
			$this->okt->logAdmin->add(array(
				'user_id' => $rs->id,
				'username' => $username,
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
		$query =
		'DELETE FROM '.$this->t_online.' '.
		'WHERE user_id='.$this->infos->f('id');

		if (!$this->db->execute($query)) {
			return false;
		}

		# Update last_visit (make sure there's something to update it with)
		if ($this->infos->f('logged') != '')
		{
			$query =
			'UPDATE '.$this->t_users.' SET '.
			'last_visit='.$this->infos->f('logged').' '.
			'WHERE id='.$this->infos->f('id');

			if (!$this->db->execute($query)) {
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
	 * Envoi un email avec un nouveau mot de passe à l'email $email
	 *
	 * @param    string    email    L'adresse email où envoyer le nouveau mot de passe
	 * @return boolean
	 */
	public function forgetPassword($email, $activate_page)
	{
		$email = strtolower(trim($email));

		# validation de l'adresse fournie
		if (!text::isEmail($email))
		{
			$this->error->set('Adresse courriel invalide.');
			return false;
		}

		# récupération des infos de l'utilisateur
		$query =
		'SELECT id, username, lastname, firstname, salt '.
		'FROM '.$this->t_users.' '.
		'WHERE email=\''.$this->db->escapeStr($email).'\'';

		if (($rs = $this->db->select($query)) === false) {
			return false;
		}

		if ($rs->isEmpty()) {
			$this->error->set('Adresse courriel inconnue.');
			return false;
		}

		while ($rs->fetch())
		{
			# génération du nouveau mot de passe et du code d'activation
			$new_password = util::random_key(8,true);
			$new_password_key = util::random_key(8);

			$password_hash = password::hash($new_password, PASSWORD_DEFAULT);

			$query =
			'UPDATE '.$this->t_users.' SET '.
				'activate_string=\''.$password_hash.'\', '.
				'activate_key=\''.$new_password_key.'\' '.
			'WHERE id='.(integer)$rs->id;

			if (!$this->db->execute($query)) {
				return false;
			}

			# Initialisation du mailer et envoi du mail
			$oMail = new oktMail($this->okt);
			$oMail->setFrom();
			$oMail->message->setTo($email);

			$oMail->useFile(OKT_LOCALES_PATH.'/'.$this->okt->user->language.'/templates/activate_password.tpl', array(
				'SITE_TITLE' => util::getSiteTitle(),
				'SITE_URL' => $this->okt->config->app_url,
				'USERNAME' => self::getUserCN($rs->username, $rs->lastname, $rs->firstname),
				'NEW_PASSWORD' => $new_password,
				'ACTIVATION_URL' => $activate_page.'?action=validate_password&uid='.$rs->id.'&key='.rawurlencode($new_password_key),
			));

			$oMail->send();
		}

		return true;
	}


	public function validatePasswordKey($user_id, $key)
	{
		# récupération des infos de l'utilisateur
		$query =
		'SELECT activate_string, activate_key '.
		'FROM '.$this->t_users.' '.
		'WHERE id='.(integer)$user_id.' ';

		if (($rs = $this->db->select($query)) === false) {
			return false;
		}

		if ($rs->isEmpty()) {
			$this->error->set('Utilisateur inconnu.');
			return false;
		}

		if ($key != $rs->activate_key) {
			$this->error->set('La clé de validation ne correspond pas.');
			return false;
		}

		$query =
		'UPDATE '.$this->t_users.' SET '.
			'password=\''.$rs->activate_string.'\', '.
			'activate_string=NULL, '.
			'activate_key=NULL '.
		'WHERE id='.(integer)$user_id.' ';

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}


	/* Language methods
	----------------------------------------------------------*/

	/**
	 * Initialisation de la langue de l'utilisateur
	 */
	public function initLanguage($cookie_name='otk_language')
	{
		$this->cookie_lang_name = $cookie_name;

		$this->infos->set('language', $this->getDefaultLang());

		$this->setUserLang($this->infos->f('language'));
	}

	/**
	 * Change la langue de l'utilisateur
	 *
	 * @return void
	 */
	public function setUserLang($lang)
	{
		if ($this->infos->f('language') === $lang) {
			return false;
		}

		if (!$this->okt->languages->isActive($lang)) {
			return false;
		}

		$this->infos->set('language', $lang);
		$this->setLangCookie($lang);

		if (!$this->infos->f('is_guest'))
		{
			$query =
			'UPDATE '.$this->t_users.' SET '.
				'language=\''.$this->db->escapeStr($lang).'\' '.
			'WHERE id='.(integer)$this->infos->f('id');

			if (!$this->db->execute($query)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Retrouve la langue de l'utilisateur
	 *
	 * @return string
	 */
	protected function getDefaultLang()
	{
		$sLang = '';

		if (isset($_COOKIE[$this->cookie_lang_name])) {
			$sLang = $_COOKIE[$this->cookie_lang_name];
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
	 * @param $value
	 * @param $expire
	 * @return void
	 */
	public function setAuthCookie($value, $expire)
	{
		setcookie($this->cookie_name, $value, $expire, $this->cookie_path, $this->cookie_domain, $this->cookie_secure, true);
	}

	/**
	 * Set a cookie auth from
	 *
	 * @param $value
	 * @return void
	 */
	public function setAuthFromCookie($value)
	{
		setcookie($this->cookie_from_name, $value, 0, $this->cookie_path, $this->cookie_domain, $this->cookie_secure, true);
	}

	/**
	 * Set a cookie lang
	 *
	 * @param $value
	 * @param $expire
	 * @return void
	 */
	public function setLangCookie($value, $expire=null)
	{
		if ($expire === null) {
			$expire = time() + self::remember_time;
		}

		setcookie($this->cookie_lang_name, $value, $expire, $this->cookie_path, $this->cookie_domain, $this->cookie_secure, true);
	}

	/**
	 * Static function that returns user's common name given to his
	 * <var>username</var>, <var>lastname</var>, <var>firstname</var> and
	 * <var>displayname</var>.
	 *
	 * @param	username		string	User name
	 * @param	lastname		string	User's last name
	 * @param	firstname		string	User's first name
	 * @param	displayname		string	User's display name
	 * @return	string
	*/
	public static function getUserCN($username, $lastname, $firstname, $displayname=null)
	{
		if (!empty($displayname)) {
			return $displayname;
		}

		if (!empty($lastname))
		{
			if (!empty($firstname)) {
				return $firstname.' '.$lastname;
			}

			return $lastname;
		}
		elseif (!empty($firstname)) {
			return $firstname;
		}

		return $username;
	}

	public function get_current_url($max_length=0)
	{
		$url = $this->okt->config->self_uri;

		if ($max_length == 0 || strlen($url) <= $max_length) {
			return $url;
		}

		// We can't find a short enough url
		return null;
	}


	public function genTmpId($create_new = true)
	{
		if (isset($_COOKIE['otk_auth_tmp_id'])) {
			$this->infos->set('tmp_id', $_COOKIE['otk_auth_tmp_id']);
		}
		else
		{
			if ($create_new)
			{
				$expire = time() + self::remember_time;
				$user_tmp_id = 'tmp_'.uniqid();
				setcookie('otk_auth_tmp_id', $user_tmp_id, $expire, $this->cookie_path, $this->cookie_domain, $this->cookie_secure, true);
				$this->infos->set('tmp_id', $user_tmp_id);
			}
			else {
				$this->infos->set('tmp_id', '');
			}
		}
	}

	public function deleteTmpId()
	{
		$expire = time() - self::remember_time;
		setcookie('otk_auth_tmp_id', '', $expire, $this->cookie_path, $this->cookie_domain, $this->cookie_secure, true);
		$this->infos->set('tmp_id', '');
	}


} # class oktAuth
