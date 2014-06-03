<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Users;

use Okatea\Tao\ApplicationShortcuts;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Users\Users;

/**
 * Le gestionnaire d'authentification de l'utilisateur en cours.
 */
class Visitor extends ApplicationShortcuts
{
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
	 * @var object recordset
	 */
	public $infos = null;

	/**
	 * Constructeur.
	 *
	 * @param object $okt
	 *        	application instance.
	 * @param string $sCookieName
	 *        	Le nom du cookie d'authentification (otk_auth)
	 * @param string $sCookiePath
	 *        	Le chemin du cookie d'authentification ('/')
	 * @param string $sCookieDomain
	 *        	Le domaine du cookie d'authentification ('')
	 * @param boolean $bCookieSecure
	 *        	Cookie d'authentification sur protocole sécurisé (false)
	 * @return void
	 */
	public function __construct($okt, $sCookieName = 'otk_auth', $sCookieFromName = 'otk_auth_from', $sCookiePath = '/', $sCookieDomain = '', $bCookieSecure = false)
	{
		parent::__construct($okt);

		$this->sUsersTable = $this->db->prefix . 'core_users';
		$this->sGroupsTable = $this->db->prefix . 'core_users_groups';
		$this->sGroupsL10nTable = $this->db->prefix . 'core_users_groups_locales';

		$this->setVisitTimeout($this->okt->config->user_visit['timeout']);
		$this->setVisitRememberTime($this->okt->config->user_visit['remember_time']);

		$this->sCookieName = $sCookieName;
		$this->sCookieFromName = $sCookieFromName;
		$this->sCookiePath = $sCookiePath;
		$this->sCookieDomain = $sCookieDomain;
		$this->bCookieSecure = $bCookieSecure;

		$this->authentication();

		$this->initLanguage($this->okt->options->get('cookie_language'));
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
	 * @param
	 *        	$id
	 * @return array
	 */
	public function getData($id = null)
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
		$iTsExpire = $iTsNow + $this->iVisitRememberTime;

		# Nous supposons qu'il est un invité
		$aCookie = array(
			'user_id' => 1,
			'password_hash' => 'Guest',
			'expiration_time' => 0,
			'expire_hash' => 'Guest'
		);

		# Si un cookie est disponible, on récupère le hash user_id et le mot de passe de lui
		if (isset($_COOKIE[$this->sCookieName]))
		{
			list ($aCookie['user_id'], $aCookie['password_hash'], $aCookie['expiration_time'], $aCookie['expire_hash']) = explode('|', base64_decode($_COOKIE[$this->sCookieName]));
		}

		# Si c'est un cookie d'un utilisateur connecté il ne devrait pas avoir déjà expiré
		if (intval($aCookie['user_id']) > 1 && intval($aCookie['expiration_time']) > $iTsNow)
		{
			$this->authenticateUser(intval($aCookie['user_id']), $aCookie['password_hash']);

			# Nous validons maintenans le hash du cookie
			if ($aCookie['expire_hash'] !== sha1($this->infos->f('password') . intval($aCookie['expiration_time'])))
			{
				$this->setDefaultUser();
			}

			# Si nous sommes retournés à l'utilisateur par défaut, la connexion a échouée
			if ($this->infos->f('id') == '1')
			{
				$this->setAuthCookie(base64_encode('1|' . Utilities::random_key(8, false, true) . '|' . $iTsExpire . '|' . Utilities::random_key(8, false, true)), $iTsExpire);
				return;
			}

			# Envoit d'un nouveau cookie mis à jour avec un nouveau timestamp d'expiration
			$iTsExpire = (intval($aCookie['expiration_time']) > $iTsNow + $this->iVisitTimeout) ? $iTsNow + $this->iVisitRememberTime : $iTsNow + $this->iVisitTimeout;
			$this->setAuthCookie(base64_encode($this->infos->f('id') . '|' . $this->infos->f('password') . '|' . $iTsExpire . '|' . sha1($this->infos->f('password') . $iTsExpire)), $iTsExpire);

			$this->infos->set('is_guest', false);
			$this->infos->set('is_admin', ($this->infos->f('group_id') == Groups::SUPERADMIN || $this->infos->f('group_id') == Groups::ADMIN));
			$this->infos->set('is_superadmin', ($this->infos->f('group_id') == Groups::SUPERADMIN));
		}
		# sinon l'utilisateur n'est plus connecté
		else
		{
			$this->setDefaultUser();
		}

		# Store common name
		$this->infos->set('usedname', Users::getUserDisplayName($this->infos->username, $this->infos->lastname, $this->infos->firstname, $this->infos->displayname));

		# And finally, store perms array
		if ($this->infos->f('perms') != '' && ! is_array($this->infos->perms))
		{
			$this->infos->set('perms', json_decode($this->infos->perms));
		}
		elseif (! is_array($this->infos->f('perms')))
		{
			$this->infos->set('perms', array());
		}
	}

	/**
	 * Méthode d'authentification de l'utilisateur courant.
	 *
	 * @param
	 *        	$mUser
	 * @param
	 *        	$sPasswordHash
	 * @return void
	 */
	public function authenticateUser($mUser, $sPasswordHash)
	{
		$sQuery =
			'SELECT u.*, g.* ' .
			'FROM ' . $this->sUsersTable . ' AS u ' .
				'INNER JOIN ' . $this->sGroupsTable . ' AS g ON g.group_id=u.group_id ' .
			'WHERE u.status = 1 AND ';

		if (Utilities::isInt($mUser))
		{
			$sQuery .= 'u.id=' . (integer) $mUser . ' ';
		}
		else
		{
			$sQuery .= 'u.username=\'' . $this->db->escapeStr($mUser) . '\' ';
		}

		if (($rs = $this->db->select($sQuery)) === false)
		{
			return false;
		}

		if ($rs->isEmpty() || ($sPasswordHash != $rs->f('password')))
		{
			$this->setDefaultUser();
		}
		else
		{
			$this->infos = $rs;
		}
	}

	/**
	 * Set default guest user informations.
	 *
	 * @return void
	 */
	public function setDefaultUser()
	{
		$sRemoteAddr = $this->okt->request->getClientIp();

		# Fetch guest user
		$sQuery =
			'SELECT u.*, g.* ' .
			'FROM ' . $this->sUsersTable . ' AS u ' .
				'INNER JOIN ' . $this->sGroupsTable . ' AS g ON g.group_id=u.group_id ' .
			'WHERE u.id=1';

		if (($rs = $this->db->select($sQuery)) === false)
		{
			return false;
		}

		if ($rs->isEmpty())
		{
			return false;
		}
		else
		{
			$this->infos = $rs;
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
	public function login($sUsername, $sPassword, $save_pass = false)
	{
		$sQuery = 'SELECT id, group_id, password ' . 'FROM ' . $this->sUsersTable . ' ' . 'WHERE username=\'' . $this->db->escapeStr($sUsername) . '\' ';

		if (($rs = $this->db->select($sQuery)) === false)
		{
			return false;
		}

		if ($rs->isEmpty())
		{
			$this->error->set(__('c_c_auth_unknown_user'));
			return false;
		}

		$sPasswordHash = $rs->password;

		if (! password_verify($sPassword, $sPasswordHash))
		{
			$this->error->set(__('c_c_auth_wrong_password'));
			return false;
		}
		elseif (password_needs_rehash($sPasswordHash, PASSWORD_DEFAULT))
		{
			$sPasswordHash = password_hash($sPassword, PASSWORD_DEFAULT);

			$sQuery = 'UPDATE ' . $this->sUsersTable . ' SET ' . 'password=\'' . $this->db->escapeStr($sPasswordHash) . '\' ' . 'WHERE id=' . $rs->id;

			if (! $this->db->execute($sQuery))
			{
				return false;
			}
		}

		if ($rs->group_id == Groups::UNVERIFIED)
		{
			$this->error->set(__('c_c_auth_account_awaiting_validation'));
			return false;
		}

		$iTsExpire = ($save_pass) ? time() + $this->iVisitRememberTime : time() + $this->iVisitTimeout;
		$this->setAuthCookie(base64_encode($rs->id . '|' . $sPasswordHash . '|' . $iTsExpire . '|' . sha1($sPasswordHash . $iTsExpire)), $iTsExpire);

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
		$this->okt->triggers->callTrigger('userLogin', $rs);

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
		if ($this->infos->f('logged') != '')
		{
			$sQuery = 'UPDATE ' . $this->sUsersTable . ' SET ' . 'last_visit=' . $this->infos->f('logged') . ' ' . 'WHERE id=' . $this->infos->f('id');

			if (! $this->db->execute($sQuery))
			{
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
		if ($this->infos->f('language') === $sLanguage)
		{
			return false;
		}

		if (! $this->okt->languages->isActive($sLanguage))
		{
			return false;
		}

		$this->infos->set('language', $sLanguage);
		$this->setLangCookie($sLanguage);

		if (! $this->infos->f('is_guest'))
		{
			$sQuery = 'UPDATE ' . $this->sUsersTable . ' SET ' . 'language=\'' . $this->db->escapeStr($sLanguage) . '\' ' . 'WHERE id=' . (integer) $this->infos->f('id');

			if (! $this->db->execute($sQuery))
			{
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
		$sLang = null;

		if (isset($_COOKIE[$this->sCookieLangName]))
		{
			$sLang = $_COOKIE[$this->sCookieLangName];
		}
		else
		{
			$sLang = $this->okt->request->getPreferredLanguage();
		}

		if ($this->okt->languages->isActive($sLang))
		{
			return $sLang;
		}
		else
		{
			return $this->okt->config->language;
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
	 * @param
	 *        	$sValue
	 * @param
	 *        	$iExpire
	 * @return void
	 */
	public function setAuthCookie($sValue, $iExpire)
	{
		setcookie($this->sCookieName, $sValue, $iExpire, $this->sCookiePath, $this->sCookieDomain, $this->bCookieSecure, true);
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
		setcookie($this->sCookieFromName, $sValue, 0, $this->sCookiePath, $this->sCookieDomain, $this->bCookieSecure, true);
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
		if ($iExpire === null)
		{
			$iExpire = time() + $this->iVisitRememberTime;
		}

		setcookie($this->sCookieLangName, $sValue, $iExpire, $this->sCookiePath, $this->sCookieDomain, $this->bCookieSecure, true);
	}
}
