<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Website;

use Okatea\Tao\Misc\Mailer;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Users\Authentification;
use Okatea\Tao\Users\Groups;
use Okatea\Tao\Users\Users;
use Okatea\Website\Controller as BaseController;

class Controller extends BaseController
{
	protected $sRedirectUrl;
	protected $sUserId = '';
	protected $aUserRegisterData = array();
	protected $aCivilities = array();
	protected $rsAdminFields = null;
	protected $rsUserFields = null;

	/**
	 * Constructor.
	 *
	 */
	public function __construct($okt)
	{
		parent::__construct($okt);

		$this->page->meta_description = $this->page->getSiteMetaDesc();

		$this->page->meta_keywords = $this->page->getSiteMetaKeywords();

		$this->defineRedirectUrl();
	}

	/**
	 * Affichage de la page d'identification.
	 *
	 */
	public function usersLogin()
	{
		# page désactivée ?
		if (!$this->okt->users->config->enable_login_page) {
			return $this->serve404();
		}

		# allready logged
		$this->handleGuest();

		$this->performLogin();

		# title tag
		$this->page->addTitleTag(__('c_c_auth_login'));

		# titre de la page
		$this->page->setTitle(__('c_c_auth_login'));

		# titre SEO de la page
		$this->page->setTitleSeo(__('c_c_auth_login'));

		# fil d'ariane
		if (!$this->isHomePageRoute()) {
			$this->page->breadcrumb->add(__('c_c_auth_login'), $this->okt->router->generateLoginUrl());
		}

		# affichage du template
		return $this->render($this->okt->users->getLoginTplPath(), array(
			'user_id' => $this->sUserId,
			'redirect' => $this->sRedirectURL
		));
	}

	/**
	 * Affichage de la page de déconnexion.
	 *
	 */
	public function usersLogout()
	{
		# déconnexion et redirection
		$this->okt->user->logout();

		$this->performRedirect();
	}

	/**
	 * Affichage de la page d'inscription.
	 *
	 */
	public function usersRegister()
	{
		# page désactivée ?
		if (!$this->okt->users->config->enable_register_page) {
			return $this->serve404();
		}

		# allready logged
		$this->handleGuest();

		$this->performRegister();

		# title tag
		$this->page->addTitleTag(__('c_c_auth_register'));

		# titre de la page
		$this->page->setTitle(__('c_c_auth_register'));

		# titre SEO de la page
		$this->page->setTitleSeo(__('c_c_auth_register'));

		# fil d'ariane
		if (!$this->isHomePageRoute()) {
			$this->page->breadcrumb->add(__('c_c_auth_register'), $this->okt->router->generateLoginUrl());
		}

		# affichage du template
		return $this->render($this->okt->users->getRegisterTplPath(), array(
			'aUsersGroups' => $this->getGroups(),
			'aTimezone' => \dt::getZones(true,true),
			'aLanguages' => $this->getLanguages(),
			'aCivilities' => $this->getCivities(false),
			'aUserRegisterData' => $this->aUserRegisterData,
			'redirect' => $this->sRedirectURL,
			'rsUserFields' => $this->rsUserFields
		));
	}

	/**
	 * Affichage de la page d'identification et d'inscription unifiée.
	 *
	 */
	public function usersLoginRegister()
	{
		# page désactivée ?
		if (!$this->okt->users->config->enable_login_page || !$this->okt->users->config->enable_register_page) {
			return $this->serve404();
		}

		# allready logged
		$this->handleGuest();

		$this->performLogin();

		$this->performRegister();

		# title tag
		$this->page->addTitleTag(__('c_c_auth_login').' / '.__('c_c_auth_register'));

		# titre de la page
		$this->page->setTitle(__('c_c_auth_login').' / '.__('c_c_auth_register'));

		# titre SEO de la page
		$this->page->setTitleSeo(__('c_c_auth_login').' / '.__('c_c_auth_register'));

		# fil d'ariane
		if (!$this->isHomePageRoute()) {
			$this->page->breadcrumb->add(__('c_c_auth_login').' / '.__('c_c_auth_register'), '');
		}

		# affichage du template
		return $this->render($this->okt->users->getLoginRegisterTplPath(), array(
			'aUsersGroups' => $this->getGroups(),
			'aTimezone' => \dt::getZones(true,true),
			'aLanguages' => $this->getLanguages(),
			'aUserRegisterData' => $this->aUserRegisterData,
			'user_id' => $this->sUserId,
			'redirect' => $this->sRedirectURL
		));
	}

	/**
	 * Affichage de la page de récupération de mot de passe perdu.
	 *
	 */
	public function usersForgetPassword()
	{
		# page désactivée ?
		if (!$this->okt->users->config->enable_forget_password_page) {
			return $this->serve404();
		}

		# allready logged
		if (!$this->okt->user->is_guest && !defined('OKT_DONT_REDIRECT_IF_LOGGED')) {
			$this->performRedirect();
		}

		$password_sended = false;
		$password_updated = false;

		if (!empty($_POST['form_sent']) && !empty($_POST['email']))
		{
			if ($this->okt->user->forgetPassword($_POST['email'], $this->generateUrl('usersForgetPassword', null, true))) {
				$password_sended = true;
			}
		}

		if (!empty($_GET['action']) && $_GET['action'] == 'validate_password' && !empty($_GET['key']) && !empty($_GET['uid']))
		{
			$uid = intval($_GET['uid']);
			$key = $_GET['key'];

			if ($this->okt->user->validatePasswordKey($uid, $key)) {
				$password_updated = true;
			}
		}

		# title tag
		$this->page->addTitleTag(__('c_c_auth_request_password'));

		# titre de la page
		$this->page->setTitle(__('c_c_auth_request_password'));

		# titre SEO de la page
		$this->page->setTitleSeo(__('c_c_auth_request_password'));

		# fil d'ariane
		if (!$this->isHomePageRoute()) {
			$this->page->breadcrumb->add(__('c_c_auth_request_password'), $this->generateUrl('usersForgetPassword'));
		}

		# affichage du template
		return $this->render($this->okt->users->getForgottenPasswordTplPath(), array(
			'password_sended' => $password_sended,
			'password_updated' => $password_updated
		));
	}

	/**
	 * Affichage de la page de profil utilisateur.
	 *
	 */
	public function usersProfile()
	{
		# page désactivée ?
		if (!$this->okt->users->config->enable_profile_page) {
			return $this->serve404();
		}

		# invité non convié
		if ($this->okt->user->is_guest) {
			return $this->redirect($this->okt->router->generateLoginUrl($this->generateUrl('usersProfile')));
		}

		# données utilisateur
		$rsUser = $this->okt->users->getUser($this->okt->user->id);

		$aUserProfilData = array(
			'id' => $this->okt->user->id,
			'username' => $rsUser->username,
			'email' => $rsUser->email,
			'civility' => $rsUser->civility,
			'lastname' => $rsUser->lastname,
			'firstname' => $rsUser->firstname,
			'language' => $rsUser->language,
			'timezone' => $rsUser->timezone,
			'password' => '',
			'password_confirm' => ''
		);

		unset($rsUser);

		# Champs personnalisés
		$aPostedData = array();
		$aFieldsValues = array();

		if ($this->okt->users->config->enable_custom_fields)
		{
			$this->rsAdminFields = $this->okt->users->fields->getFields(array(
				'status' => true,
				'admin_editable' => true,
				'language' => $this->okt->user->language
			));

			# Liste des champs utilisateur
			$this->rsUserFields = $this->okt->users->fields->getFields(array(
				'status' => true,
				'user_editable' => true,
				'language' => $this->okt->user->language
			));

			# Valeurs des champs
			$rsFieldsValues = $this->okt->users->fields->getUserValues($this->okt->user->id);

			while ($rsFieldsValues->fetch()) {
				$aFieldsValues[$rsFieldsValues->field_id] = $rsFieldsValues->value;
			}

			# Initialisation des données des champs
			while ($this->rsUserFields->fetch())
			{
				switch ($this->rsUserFields->type)
				{
					default:
					case 1 : # Champ texte
					case 2 : # Zone de texte
						$aPostedData[$this->rsUserFields->id] = !empty($_POST[$this->rsUserFields->html_id]) ? $_POST[$this->rsUserFields->html_id] : (!empty($aFieldsValues[$this->rsUserFields->id]) ? $aFieldsValues[$this->rsUserFields->id] : '');
					break;

					case 3 : # Menu déroulant
						$aPostedData[$this->rsUserFields->id] = isset($_POST[$this->rsUserFields->html_id]) ? $_POST[$this->rsUserFields->html_id] : (!empty($aFieldsValues[$this->rsUserFields->id]) ? $aFieldsValues[$this->rsUserFields->id] : '');
					break;

					case 4 : # Boutons radio
						$aPostedData[$this->rsUserFields->id] = isset($_POST[$this->rsUserFields->html_id]) ? $_POST[$this->rsUserFields->html_id] : (!empty($aFieldsValues[$this->rsUserFields->id]) ? $aFieldsValues[$this->rsUserFields->id] : '');
					break;

					case 5 : # Cases à cocher
						$aPostedData[$this->rsUserFields->id] = !empty($_POST[$this->rsUserFields->html_id]) && is_array($_POST[$this->rsUserFields->html_id]) ? $_POST[$this->rsUserFields->html_id] : (!empty($aFieldsValues[$this->rsUserFields->id]) ? $aFieldsValues[$this->rsUserFields->id] : '');
					break;
				}
			}
		}

		# Suppression des cookies
		if (!empty($_REQUEST['cookies']))
		{
			$aCookies = array_keys($_COOKIE);
			unset($aCookies[$okt->options->get('cookie_auth_name')]);

			foreach ($aCookies as $c)
			{
				unset($_COOKIE[$c]);
				setcookie($c,null);
			}

			return $this->redirect($this->generateUrl('usersProfile'));
		}

		# Formulaire de changement de mot de passe
		if (!empty($_POST['change_password']) && $this->okt->checkPerm('change_password'))
		{
			$aUserProfilData['password'] = !empty($_POST['edit_password']) ? $_POST['edit_password'] : '';
			$aUserProfilData['password_confirm'] = !empty($_POST['edit_password_confirm']) ? $_POST['edit_password_confirm'] : '';

			$this->okt->users->changeUserPassword($aUserProfilData);

			return $this->redirect($this->generateUrl('usersProfile'));
		}

		# Formulaire de modification de l'utilisateur envoyé
		if (!empty($_POST['form_sent']))
		{
			$aUserProfilData = array(
				'id' => $this->okt->user->id,
				'username' => isset($_POST['edit_username']) ? $_POST['edit_username'] : '',
				'email' => isset($_POST['edit_email']) ? $_POST['edit_email'] : '',
				'civility' => isset($_POST['edit_civility']) ? $_POST['edit_civility'] : '',
				'lastname' => isset($_POST['edit_lastname']) ? $_POST['edit_lastname'] : '',
				'firstname' => isset($_POST['edit_firstname']) ? $_POST['edit_firstname'] : '',
				'language' => isset($_POST['edit_language']) ? $_POST['edit_language'] : '',
				'timezone' => isset($_POST['edit_timezone']) ? $_POST['edit_timezone'] : ''
			);

			if ($this->okt->config->users_registration['merge_username_email']) {
				$aUserProfilData['username'] = $aUserProfilData['email'];
			}

			# peuplement et vérification des champs personnalisés obligatoires
			if ($this->okt->users->config->enable_custom_fields) {
				$this->okt->users->fields->getPostData($this->rsUserFields, $aPostedData);
			}

			if ($this->okt->users->updUser($aUserProfilData))
			{
				# -- CORE TRIGGER : adminModUsersProfileProcess
				$this->okt->triggers->callTrigger('adminModUsersProfileProcess', $_POST);

				if ($this->okt->users->config->enable_custom_fields)
				{
					while ($this->rsUserFields->fetch()) {
						$this->okt->users->fields->setUserValues($this->okt->user->id, $this->rsUserFields->id, $aPostedData[$this->rsUserFields->id]);
					}
				}

				return $this->redirect($this->generateUrl('usersProfile'));
			}
		}

		# fuseaux horraires
		$aTimezone = \dt::getZones(true,true);

		# langues
		$aLanguages = $this->getLanguages();

		# title tag
		$this->page->addTitleTag(__('c_c_user_profile'));

		# titre de la page
		$this->page->setTitle(__('c_c_user_profile'));

		# titre SEO de la page
		$this->page->setTitleSeo(__('c_c_user_profile'));

		# fil d'ariane
		if (!$this->isHomePageRoute()) {
			$this->page->breadcrumb->add(__('c_c_user_profile'), $this->generateUrl('usersProfile'));
		}

		# affichage du template
		return $this->render($this->okt->users->getProfileTplPath(), array(
			'aUserProfilData' => $aUserProfilData,
			'aTimezone' => $aTimezone,
			'aLanguages' => $aLanguages,
			'aCivilities' => $this->getCivities(false),
			'rsAdminFields' => $this->rsAdminFields,
			'rsUserFields' => $this->rsUserFields,
			'aPostedData' => $aPostedData,
			'aFieldsValues' => $aFieldsValues
		));
	}

	/**
	 * Définit l'URL de redirection.
	 *
	 */
	protected function defineRedirectUrl()
	{
		$sRequestRedirectUrl = $this->request->request->get('redirect', $this->request->query->get('redirect'));

		if (!empty($sRequestRedirectUrl))
		{
			$sRedirectUrl = rawurldecode($sRequestRedirectUrl);
			$this->session->set('okt_redirect_url', $sRedirectUrl);
		}
		elseif ($this->session->has('okt_redirect_url')) {
			$sRedirectUrl = $this->session->get('okt_redirect_url');
		}
		else {
			$sRedirectUrl = $this->page->generateUrl('homePage');
		}

		$this->sRedirectURL = $sRedirectUrl;
	}

	/**
	 * Supprime l'URL de redirection en session.
	 *
	 */
	protected function unsetSessionRedirectUrl()
	{
		if ($this->session->has('okt_redirect_url')) {
			$this->session->remove('okt_redirect_url');
		}
	}

	/**
	 * Réalise une redirection.
	 *
	 */
	protected function performRedirect()
	{
		$this->unsetSessionRedirectUrl();
		return $this->redirect($this->sRedirectURL);
	}

	/**
	 * Redirige l'utilisateur si il est logué.
	 *
	 */
	protected function handleGuest()
	{
		if (!$this->okt->user->is_guest && !defined('OKT_DONT_REDIRECT_IF_LOGGED')) {
			$this->performRedirect();
		}
	}

	/**
	 * Réalise une connexion.
	 *
	 */
	protected function performLogin()
	{
		if (!empty($_POST['sended']) && empty($_POST['user_id']) && empty($_POST['user_pwd'])) {
			$this->okt->error->set(__('c_c_auth_please_enter_username_password'));
		}
		else if (!empty($_POST['user_id']) && !empty($_POST['user_pwd']))
		{
			$this->sUserId = $_POST['user_id'];
			$user_remember = !empty($_POST['user_remember']) ? true : false;

			if ($this->okt->user->login($this->sUserId, $_POST['user_pwd'], $user_remember)) {
				$this->performRedirect();
			}
		}
		else {
			$this->sUserId = '';
		}
	}

	/**
	 * Réalise une inscription.
	 *
	 */
	protected function performRegister()
	{
		# default data
		$this->aUserRegisterData = array(
			'civility' => 1,
			'username' => '',
			'lastname' => '',
			'firstname' => '',
			'password' => '',
			'password_confirm' => '',
			'email' => '',
			'group_id' => $this->okt->config->users_registration['default_group'],
			'timezone' => $this->okt->config->timezone,
			'language' => $this->okt->config->language
		);

		# Champs personnalisés
		if ($this->okt->users->config->enable_custom_fields)
		{
			$aPostedData = array();

			# Liste des champs
			$this->rsUserFields = $this->okt->users->fields->getFields(array(
				'status' => true,
				'user_editable' => true,
				'register' => true,
				'language' => $this->okt->user->language
			));

			# Valeurs des champs
			$rsFieldsValues = $this->okt->users->fields->getUserValues($this->okt->user->id);
			$aFieldsValues = array();
			while ($rsFieldsValues->fetch()) {
				$aFieldsValues[$rsFieldsValues->field_id] = $rsFieldsValues->value;
			}

			# Initialisation des données des champs
			while ($this->rsUserFields->fetch())
			{
				switch ($this->rsUserFields->type)
				{
					default:
					case 1 : # Champ texte
					case 2 : # Zone de texte
						$aPostedData[$this->rsUserFields->id] = !empty($_POST[$this->rsUserFields->html_id]) ? $_POST[$this->rsUserFields->html_id] : (!empty($aFieldsValues[$this->rsUserFields->id]) ? $aFieldsValues[$this->rsUserFields->id] : '');
					break;

					case 3 : # Menu déroulant
						$aPostedData[$this->rsUserFields->id] = isset($_POST[$this->rsUserFields->html_id]) ? $_POST[$this->rsUserFields->html_id] : (!empty($aFieldsValues[$this->rsUserFields->id]) ? $aFieldsValues[$this->rsUserFields->id] : '');
					break;

					case 4 : # Boutons radio
						$aPostedData[$this->rsUserFields->id] = isset($_POST[$this->rsUserFields->html_id]) ? $_POST[$this->rsUserFields->html_id] : (!empty($aFieldsValues[$this->rsUserFields->id]) ? $aFieldsValues[$this->rsUserFields->id] : '');
					break;

					case 5 : # Cases à cocher
						$aPostedData[$this->rsUserFields->id] = !empty($_POST[$this->rsUserFields->html_id]) && is_array($_POST[$this->rsUserFields->html_id]) ? $_POST[$this->rsUserFields->html_id] : (!empty($aFieldsValues[$this->rsUserFields->id]) ? $aFieldsValues[$this->rsUserFields->id] : '');
					break;
				}
			}
		}

		# ajout d'un utilisateur
		if (!empty($_POST['add_user']))
		{
			$this->aUserRegisterData = array(
				'active' => 1,
				'username' => !empty($_POST['add_username']) ? $_POST['add_username'] : '',
				'lastname' => !empty($_POST['add_lastname']) ? $_POST['add_lastname'] : '',
				'firstname' => !empty($_POST['add_firstname']) ? $_POST['add_firstname'] : '',
				'password' => !empty($_POST['add_password']) ? $_POST['add_password'] : '',
				'password_confirm' => !empty($_POST['add_password_confirm']) ? $_POST['add_password_confirm'] : '',
				'email' => !empty($_POST['add_email']) ? $_POST['add_email'] : '',
				'group_id' => ($this->okt->config->users_registration['user_choose_group'] && !empty($_POST['add_group_id']) && in_array($_POST['add_group_id'],$this->getGroups())) ? $_POST['add_group_id'] : $this->okt->config->users_registration['default_group'],
				'timezone' => !empty($_POST['add_timezone']) ? $_POST['add_timezone'] : $this->okt->config->timezone,
				'language' => !empty($_POST['add_language']) && in_array($_POST['add_language'], $this->getLanguages()) ? $_POST['add_language'] : $this->okt->config->language,
				'civility' => !empty($_POST['add_civility']) ? $_POST['add_civility'] : ''
			);

			if ($this->okt->config->users_registration['merge_username_email']) {
				$this->aUserRegisterData['username'] = $this->aUserRegisterData['email'];
			}

			# vérification des champs personnalisés obligatoires
			if ($this->okt->users->config->enable_custom_fields)
			{
				while ($this->rsUserFields->fetch())
				{
					if ($this->rsUserFields->active == 2 && empty($aPostedData[$this->rsUserFields->id])) {
						$this->okt->error->set('Vous devez renseigner le champ "'.Utilities::escapeHtml($this->rsUserFields->title).'".');
					}
				}
			}

			if (($new_id = $this->okt->users->addUser($this->aUserRegisterData)) !== false)
			{
				$_POST['user_id'] = $new_id;

				# -- CORE TRIGGER : adminModUsersRegisterProcess
				$this->okt->triggers->callTrigger('adminModUsersRegisterProcess', $_POST);

				$rsUser = $this->okt->users->getUser($new_id);

				if ($this->okt->users->config->enable_custom_fields)
				{
					while ($this->rsUserFields->fetch()) {
						$this->okt->users->fields->setUserValues($new_id, $this->rsUserFields->id, $aPostedData[$this->rsUserFields->id]);
					}
				}

				# Initialisation du mailer et envoi du mail
				$oMail = new Mailer($this->okt);

				$oMail->setFrom();

				if ($this->okt->config->users_registration['validate_users_registration']) {
					$template_file = 'welcom_waiting.tpl';
				} else {
					$template_file = 'welcom.tpl';
				}

				$oMail->useFile(__DIR__.'/../locales/'.$rsUser->language.'/templates/'.$template_file, array(
					'SITE_TITLE' => $this->page->getSiteTitle($rsUser->language),
					'SITE_URL' => $this->request->getSchemeAndHttpHost().$this->okt->config->app_path,
					'USER_CN' => Authentification::getUserCN($rsUser->username, $rsUser->lastname, $rsUser->firstname),
					'USERNAME' => $rsUser->username,
					'PASSWORD' => $this->aUserRegisterData['password']
				));

				$oMail->message->setTo($rsUser->email);

				$oMail->send();


				# Initialisation du mailer et envoi du mail à l'administrateur
				if ($this->okt->config->users_registration['mail_new_registration'])
				{
					$oMail = new Mailer($this->okt);

					$oMail->setFrom();

					if ($this->okt->config->users_registration['validate_users_registration']) {
						$template_file = 'registration_validate.tpl';
					} else {
						$template_file = 'registration.tpl';
					}

					$rsAdministrators = $this->okt->users->getUsers(array('group_id'=>Groups::ADMIN));
					while ($rsAdministrators->fetch())
					{
						$oMail->useFile(__DIR__.'/../locales/'.$rsAdministrators->language.'/templates/'.$template_file, array(
							'SITE_TITLE' => $this->page->getSiteTitle($rsUser->language),
							'SITE_URL' => $this->request->getSchemeAndHttpHost().$this->okt->config->app_path,
							'USER_CN' => Authentification::getUserCN($rsUser->username, $rsUser->lastname, $rsUser->firstname),
							'PROFIL' => $this->request->getSchemeAndHttpHost().$this->okt->config->app_path.'admin/module.php?m=users&action=edit&id='.$rsUser->id
						));

						$oMail->message->setTo($rsAdministrators->email);

						$oMail->send();
					}
				}


				# eventuel connexion du nouvel utilisateur
				if (!$this->okt->config->users_registration['validate_users_registration'] && $this->okt->config->users_registration['auto_log_after_registration']) {
					$this->okt->user->login($this->aUserRegisterData['username'],$this->aUserRegisterData['password'],false);
				}

				$this->performRedirect();
			}
		}
	}

	/**
	 * Retourne la liste des groupes actif pour le commun des mortels.
	 *
	 */
	protected function getGroups()
	{
		static $aUsersGroups = null;

		if (is_array($aUsersGroups)) {
			return $aUsersGroups;
		}

		$aUsersGroups = array();

		$rsGroups = $this->okt->users->getGroups(array(
			'group_id_not' => array(
				Groups::SUPERADMIN,
				Groups::ADMIN,
				Groups::GUEST)
		));

		while ($rsGroups->fetch()) {
			$aUsersGroups[Utilities::escapeHTML($rsGroups->title)] = $rsGroups->group_id;
		}

		return $aUsersGroups;
	}

	/**
	 * Retourne la listes des langues actives.
	 *
	 */
	protected function getLanguages()
	{
		foreach ($this->okt->languages->list as $aLanguage) {
			$aLanguages[Utilities::escapeHTML($aLanguage['title'])] = $aLanguage['code'];
		}

		return $aLanguages;
	}

	/**
	 * Retourne la listes des civilités
	 *
	 */
	protected function getCivities($bEmptyField=true)
	{
		if ($bEmptyField)
		{
			return array_merge(
				array('&nbsp;'=>0),
				Users::getCivilities(true)
			);
		}

		return Users::getCivilities(true);
	}
}
