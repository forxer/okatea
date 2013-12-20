<?php
/**
 * @ingroup okt_module_users
 * @brief La classe principale du module.
 *
 */

use Tao\Admin\Page;
use Tao\Misc\Utilities as util;
use Tao\Core\Authentification;
use Tao\Admin\Menu as AdminMenu;
use Tao\Modules\Module;
use Tao\Routing\Route;

class module_users extends Module
{
	protected $t_users;
	protected $t_groups;

	protected $locales = null;

	public $config;
	public $users_dir;

	protected function prepend()
	{
		# chargement des principales locales
		$this->okt->l10n->loadFile(__DIR__.'/locales/'.$this->okt->user->language.'/main');

		# autoload
		$this->okt->autoloader->addClassMap(array(
			'UsersController' => __DIR__.'/inc/UsersController.php',
			'UsersCustomFields' => __DIR__.'/inc/UsersCustomFields.php',
			'UsersFieldRecordset' => __DIR__.'/inc/UsersFieldRecordset.php',
			'UsersFilters' => __DIR__.'/inc/UsersFilters.php',
			'UsersHelpers' => __DIR__.'/inc/UsersHelpers.php'
		));

		# permissions
		$this->okt->addPermGroup('users',__('m_users_perm_group'));
			$this->okt->addPerm('users', __('m_users_perm_global'), 'users');
			$this->okt->addPerm('users_edit', __('m_users_perm_edit'), 'users');
			$this->okt->addPerm('users_delete', __('m_users_perm_delete'), 'users');
			$this->okt->addPerm('change_password', __('m_users_perm_change_password'), 'users');
			$this->okt->addPerm('groups', __('m_users_perm_groups'), 'users');
			$this->okt->addPerm('users_custom_fields', __('m_users_perm_custom_fields'), 'users');
			$this->okt->addPerm('users_export', __('m_users_perm_export'), 'users');
			$this->okt->addPerm('users_display', __('m_users_perm_display'), 'users');
			$this->okt->addPerm('users_config', __('m_users_perm_config'), 'users');

		# les tables
		$this->t_users = $this->db->prefix.'core_users';
		$this->t_groups = $this->db->prefix.'core_users_groups';

		# config
		$this->config = $this->okt->newConfig('conf_users');

		# répertoire upload
		$this->upload_dir = OKT_UPLOAD_PATH.'/users/';
		$this->upload_url = OKT_UPLOAD_URL.'/users/';

		# custom fieds
		if ($this->config->enable_custom_fields) {
			$this->fields = new UsersCustomFields($this->okt);
		}
	}

	protected function prepend_admin()
	{
		# chargement des locales admin
		$this->okt->l10n->loadFile(__DIR__.'/locales/'.$this->okt->user->language.'/admin');

		# on détermine si on est actuellement sur ce module
		$this->onThisModule();

		# on ajoutent un item au menu principal
		if (!defined('OKT_DISABLE_MENU'))
		{
			$this->okt->page->mainMenu->add(
				__('Users'),
				'module.php?m=users',
				ON_USERS_MODULE,
				5000000,
				($this->okt->checkPerm('users')),
				null,
				($this->okt->page->usersSubMenu = new AdminMenu(null, Page::$formatHtmlSubMenu)),
				$this->url().'/icon.png'
			);
				$this->okt->page->usersSubMenu->add(
					__('c_a_menu_management'),
					'module.php?m=users&amp;action=index',
					ON_USERS_MODULE && (!$this->okt->page->action || $this->okt->page->action === 'index' || $this->okt->page->action === 'add' || $this->okt->page->action === 'edit'),
					10,
					$this->okt->checkPerm('users')
				);
				$this->okt->page->usersSubMenu->add(
					__('m_users_Groups'),
					'module.php?m=users&amp;action=groups',
					ON_USERS_MODULE && ($this->okt->page->action === 'groups'),
					20,
					$this->okt->checkPerm('groups')
				);
				$this->okt->page->usersSubMenu->add(
					__('m_users_Custom_fields'),
					'module.php?m=users&amp;action=fields',
					ON_USERS_MODULE && ($this->okt->page->action === 'fields' || $this->okt->page->action === 'field'),
					30,
					$this->config->enable_custom_fields && $this->okt->checkPerm('users_custom_fields')
				);
				$this->okt->page->usersSubMenu->add(
					__('m_users_Export'),
					'module.php?m=users&amp;action=export',
					ON_USERS_MODULE && ($this->okt->page->action === 'export'),
					40,
					$this->okt->checkPerm('users_export')
				);
				$this->okt->page->usersSubMenu->add(
					__('c_a_menu_display'),
					'module.php?m=users&amp;action=display',
					ON_USERS_MODULE && ($this->okt->page->action === 'display'),
					90,
					$this->okt->checkPerm('users_display')
				);
				$this->okt->page->usersSubMenu->add(
					__('c_a_menu_configuration'),
					'module.php?m=users&amp;action=config',
					ON_USERS_MODULE && ($this->okt->page->action === 'config'),
					100,
					$this->okt->checkPerm('users_config')
				);
		}
	}

	protected function prepend_public()
	{
		$this->okt->triggers->registerTrigger('publicAdminBarBeforeDefaultsItems',
			array('module_users', 'publicAdminBarBeforeDefaultsItems'));

		$this->okt->triggers->registerTrigger('publicAdminBarItems',
			array('module_users', 'publicAdminBarItems'));
	}

	/**
	 * Modification des URL de base de la barre admin côté publique.
	 *
	 * @param oktCore $okt
	 * @param arrayObject $aPrimaryAdminBar
	 * @param arrayObject $aSecondaryAdminBar
	 * @param arrayObject $aBasesUrl
	 * @return void
	 */
	public static function publicAdminBarBeforeDefaultsItems($okt, $aPrimaryAdminBar, $aSecondaryAdminBar, $aBasesUrl)
	{
		$aBasesUrl['logout'] = html::escapeHTML(UsersHelpers::getLogoutUrl());

		$aBasesUrl['profil'] = $aBasesUrl['admin'].'/module.php?m=users&amp;action=profil&amp;id='.$okt->user->id;
	}

	/**
	 * Ajout d'éléments à la barre admin côté publique.
	 *
	 * @param oktCore $okt
	 * @param arrayObject $aPrimaryAdminBar
	 * @param arrayObject $aSecondaryAdminBar
	 * @param arrayObject $aBasesUrl
	 * @return void
	 */
	public static function publicAdminBarItems($okt, $aPrimaryAdminBar, $aSecondaryAdminBar, $aBasesUrl)
	{
		# lien ajouter un utilisateur
		if ($okt->checkPerm('users'))
		{
			$aPrimaryAdminBar[200]['items'][1000] = array(
				'href' => $aBasesUrl['admin'].'/module.php?m=users&amp;action=add',
				'title' => __('m_users_ab_user_title'),
				'intitle' => __('m_users_ab_user')
			);
		}
	}


	/* Utilisateurs
	----------------------------------------------------------*/

	/**
	 * Renvoie les données concernant un ou plusieurs utilisateurs.
	 * La méthode renvoie false si elle échoue.
	 *
	 * @param	array	params		Les paramètres
	 * @param	boolean	count_only	Permet de ne retourner que le compte
	 * @return	recordset
	 */
	public function getUsers($aParams=array(), $count_only=false)
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
			$aWords = text::splitWords($aParams['search']);

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

		if ($count_only)
		{
			$sQuery =
			'SELECT COUNT(u.id) AS num_users '.
			'FROM '.$this->t_users.' AS u '.
			$sReqPlus;
		}
		else {
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
			return new recordset(array());
		}

		if ($count_only) {
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
	 * @param $user
	 * @return recordset
	 */
	public function getUser($user)
	{
		$aParams = array();

		if (util::isInt($user)) {
			$aParams['id'] = $user;
		}
		else {
			$aParams['username'] = $user;
		}

		return $this->getUsers($aParams);
	}

	/**
	 * Vérifie l'existence d'un utilisateur
	 *
	 * @param $user
	 * @return boolean
	 */
	public function userExists($user)
	{
		if ($this->getUser($user)->isEmpty()) {
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
		'WHERE u.registration_ip=\''.$this->db->escapeStr(http::realIP()).'\' '.
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
			$this->error->set(__('m_users_error_username_too_short'));
		}
		elseif (mb_strlen($username) > 255) {
			$this->error->set(__('m_users_error_username_too_long'));
		}
		elseif (mb_strtolower($username) == 'guest') {
			$this->error->set(__('m_users_error_reserved_username'));
		}
		elseif (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $username) || preg_match('/((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))/', $username)) {
			$this->error->set(__('m_users_error_reserved_username'));
		}
		elseif ((strpos($username, '[') !== false || strpos($username, ']') !== false) && strpos($username, '\'') !== false && strpos($username, '"') !== false) {
			$this->error->set(__('m_users_error_forbidden_characters'));
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
					$this->error->set(__('m_users_error_email_already_exist'));
				}
				else {
					$this->error->set(__('m_users_error_username_already_exist'));
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
			$this->error->set(__('m_users_must_enter_email_address'));
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
		if (!text::isEmail($sEmail)) {
			$this->error->set(sprintf(__('c_c_error_invalid_email'), html::escapeHTML($sEmail)));
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
			$this->error->set(__('m_users_must_enter_password'));
		}
		elseif (mb_strlen($aParams['password']) < 4) {
			$this->error->set(__('m_users_must_enter_password_of_at_least_4_characters'));
		}
		elseif (empty($aParams['password_confirm'])) {
			$this->error->set(__('m_users_must_confirm_password'));
		}
		elseif ($aParams['password'] != $aParams['password_confirm']) {
			$this->error->set(__('m_users_error_passwords_do_not_match'));
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
			'\''.$this->db->escapeStr(util::random_key(12)).'\', '.
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
			'salt=\''.$this->db->escapeStr(util::random_key(12)).'\' '.
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
			$this->error->set(sprintf(__('m_users_error_user_%s_not_exists'), $id));
			return false;
		}

		# si on veut supprimer un super-admin alors il faut vérifier qu'il y en as d'autres
		if ($rsUser->group_id == Authentification::superadmin_group_id)
		{
			$iCountSudo = $this->getUsers(array('group_id'=>Authentification::superadmin_group_id), true);

			if ($iCountSudo < 2)
			{
				$this->error->set(__('m_users_error_cannot_remove_last_super_administrator'));
				return false;
			}
		}

		# si on veut supprimer un admin alors il faut vérifier qu'il y en as d'autres
		if ($rsUser->group_id == Authentification::admin_group_id)
		{
			$iCountAdmin = $this->getUsers(array('group_id'=>Authentification::admin_group_id), true);

			if ($iCountAdmin < 2)
			{
				$this->error->set(__('m_users_error_cannot_remove_last_administrator'));
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
		if ($this->config->enable_custom_fields) {
			$this->fields->delUserValue($id);
		}

		# delete user directory
		$user_dir = $this->upload_dir.$id.'/';

		if (files::isDeletable($user_dir)) {
			files::deltree($user_dir);
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
			$this->error->set(sprintf(__('m_users_error_user_%s_not_exists'), $iUserId));
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
			$this->error->set(sprintf(__('m_users_error_user_%s_not_exists'), $iUserId));
			return false;
		}

		# si on veut désactiver un super-admin alors il faut vérifier qu'il y en as d'autres
		if ($iActive == 0 && $rsUser->group_id == Authentification::superadmin_group_id)
		{
			$iCountSudo = $this->getUsers(array('group_id' => Authentification::superadmin_group_id, 'active' => 1), true);

			if ($iCountSudo < 2)
			{
				$this->error->set(__('m_users_error_cannot_disable_last_super_administrator'));
				return false;
			}
		}

		# si on veut désactiver un admin alors il faut vérifier qu'il y en as d'autres
		if ($iActive == 0 && $rsUser->group_id == Authentification::admin_group_id)
		{
			$iCountAdmin = $this->getUsers(array('group_id'=>Authentification::admin_group_id, 'active' => 1), true);

			if ($iCountAdmin < 2)
			{
				$this->error->set(__('m_users_error_cannot_disable_last_administrator'));
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


	/* Groupes
	----------------------------------------------------------*/

	/**
	 * Retourne les informations de plusieurs groupes
	 *
	 * @param $param
	 * @param $count_only
	 * @return recordset
	 */
	public function getGroups($aParams=array(),$count_only=false)
	{
		$sReqPlus = '1 ';

		if (isset($aParams['group_id']))
		{
			if (is_array($aParams['group_id']))
			{
				$aParams['group_id'] = array_map('intval',$aParams['group_id']);
				$sReqPlus .= 'AND group_id IN ('.implode(',',$aParams['group_id']).') ';
			}
			else {
				$sReqPlus .= 'AND group_id='.(integer)$aParams['group_id'].' ';
			}
		}

		if (!empty($aParams['group_id_not']))
		{
			if (is_array($aParams['group_id_not']))
			{
				$aParams['group_id_not'] = array_map('intval',$aParams['group_id_not']);
				$sReqPlus .= 'AND group_id NOT IN ('.implode(',',$aParams['group_id_not']).') ';
			}
			else {
				$sReqPlus .= 'AND group_id<>'.(integer)$aParams['group_id_not'].' ';
			}
		}

		if (!empty($aParams['title'])) {
			$sReqPlus .= 'AND title=\''.$this->db->escapeStr($aParams['title']).'\' ';
		}

		if ($count_only)
		{
			$sQuery =
			'SELECT COUNT(group_id) AS num_groups '.
			'FROM '.$this->t_groups.' '.
			'WHERE '.$sReqPlus;
		}
		else {
			$sQuery =
			'SELECT group_id, title, perms '.
			'FROM '.$this->t_groups.' '.
			'WHERE '.$sReqPlus;

			if (!empty($aParams['order'])) {
				$sQuery .= 'ORDER BY '.$aParams['order'].' ';
			}
			else {
				$sQuery .= 'ORDER BY group_id ASC ';
			}

			if (!empty($aParams['limit'])) {
				$sQuery .= 'LIMIT '.$aParams['limit'].' ';
			}
		}

		if (($rs = $this->db->select($sQuery)) === false) {
			return new recordset(array());
		}

		if ($count_only) {
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
	public function getGroup($group)
	{
		$aParams = array();

		if (util::isInt($group)) {
			$aParams['group_id'] = $group;
		}
		else {
			$aParams['title'] = $group;
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
	 * @param $group_id
	 * @param $title
	 * @return boolean
	 */
	public function updGroup($group_id, $title)
	{
		if (!$this->groupExists($group_id)) {
			return false;
		}

		$sQuery =
		'UPDATE '.$this->t_groups.' SET '.
			'title=\''.$this->db->escapeStr($title).'\' '.
		'WHERE group_id='.(integer)$group_id;

		if (!$this->db->execute($sQuery)) {
			return false;
		}

		return true;
	}

	public function updGroupPerms($group_id, $perms)
	{
		if (!$this->groupExists($group_id)) {
			return false;
		}

		if (is_array($perms)) {
			$perms = serialize($perms);
		}

		$sQuery =
		'UPDATE '.$this->t_groups.' SET '.
			'perms=\''.$this->db->escapeStr($perms).'\' '.
		'WHERE group_id='.(integer)$group_id;

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
	public function deleteGroup($group_id)
	{
		if (!$this->groupExists($group_id)) {
			return false;
		}

		$nbUser = $this->getUsers(array('group_id'=>$group_id),true);

		if ($nbUser > 0)
		{
			$this->error->set(__('m_users_error_users_in_group_cannot_remove'));
			return false;
		}
		else {
			$sQuery =
			'DELETE FROM '.$this->t_groups.' '.
			'WHERE group_id='.(integer)$group_id;

			if (!$this->db->execute($sQuery)) {
				return false;
			}

			$this->db->optimize($this->t_groups);

			return true;
		}
	}


	/* Exportation
	----------------------------------------------------------*/

	public function export($format, $fields, $groups)
	{
		# build the query
		$sQuery = 'SELECT ';

		$aQueryFields = array();
		foreach ($fields as $field)
		{
			if ($field != 'title') {
				$aQueryFields[] = 'u.'.$field;
			}
		}

		if (in_array('title', $fields))
		{
			$aQueryFields[] = 'g.title';

			$sQuery .=
			implode(',',$aQueryFields).' '.
			'FROM '.$this->t_users.' AS u '.
				'LEFT JOIN '.$this->t_groups.' AS g ON g.group_id=u.group_id ';
		}
		else
		{
			$sQuery .= implode(',',$aQueryFields).' '.
			'FROM '.$this->t_users.' AS u ';
		}

		$sQuery .= 'WHERE u.group_id IN ('.implode(',',$groups).') ';


		# get the users recordset
		if (($rs = $this->db->select($sQuery)) === false) {
			$rs = new recordset(array());
		}

		$sMethod = 'exportTo'.$format;

		if (is_callable(array($this,$sMethod)))
		{
			return call_user_func(array($this,$sMethod),$rs,$fields);
			exit();
		}
		else {
			$this->error->set('Unable to call '.$sMethod.' method');
		}
	}

	protected function exportToCsvUtf8($rs,$fields)
	{
		$aAllowedFields = self::getAllowedFields();

		$result = array();

		$head = array();
		foreach ($fields as $field) {
			$head[] = '"'.$aAllowedFields[$field].'"';
		}

		$result[] = implode(';',$head);

		while ($rs->fetch())
		{
			$line = array();
			foreach ($fields as $field) {
				$line[] = '"'.$rs->$field.'"';
			}
			$result[] = implode(';',$line);
		}

		$result = implode("\r\n",$result);

		$filename = 'users-'.$this->okt->request->getSchemeAndHttpHost().$this->okt->config->app_path.'-'.date('YmdHis').'.csv';

		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Length: '.mb_strlen($result));
		header('Content-Disposition: attachment; filename='.$filename);
		echo $result;
		die;
	}

	protected function exportToCsvIso88591($rs,$fields)
	{
		$aAllowedFields = self::getAllowedFields();

		$result = array();

		$head = array();
		foreach ($fields as $field) {
			$head[] = '"'.mb_convert_encoding($aAllowedFields[$field],'ISO-8859-1','UTF-8').'"';
		}

		$result[] = implode(';',$head);

		while ($rs->fetch())
		{
			$line = array();
			foreach ($fields as $field) {
				$line[] = '"'.mb_convert_encoding($rs->$field,'ISO-8859-1','UTF-8').'"';
			}
			$result[] = implode(';',$line);
		}

		$result = implode("\r\n",$result);

		$filename = 'users-'.$this->okt->request->getSchemeAndHttpHost().$this->okt->config->app_path.'-'.date('YmdHis').'.csv';

		header('Content-Type: text/csv; charset=ISO-8859-1');
		header('Content-Length: '.mb_strlen($result,'ISO-8859-1'));
		header('Content-Disposition: attachment; filename='.$filename);
		echo $result;
		die;
	}

	protected function exportToHtml($rs,$fields)
	{
		$sTitle = 'users-'.$this->okt->request->getSchemeAndHttpHost().$this->okt->config->app_path.'-'.date('YmdHis');

		$aResult = array();
		$aResult[] =
		'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'.PHP_EOL.
		'<html xmlns="http://www.w3.org/1999/xhtml">'.PHP_EOL.
		'<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.PHP_EOL.
		'<title>'.$sTitle.'</title></head><body>'.PHP_EOL.
		'<h1>'.$sTitle.'</h1>'.PHP_EOL.
		'<table border="1" cellpadding="5" cellspacing="0"><thead><tr>';

		$aAllowedFields = self::getAllowedFields();

		foreach ($fields as $field) {
			$aResult[] = '<th>'.html::escapeHTML($aAllowedFields[$field]).'</th>';
		}

		$aResult[] = '</tr></thead><tbody>';

		while ($rs->fetch())
		{
			$aLine = array();
			foreach ($fields as $field) {
				$aLine[] = html::escapeHTML($rs->$field);
			}
			$aResult[] = '<tr><td>'.implode('</td><td>',$aLine).'</td></tr>';
		}

		$aResult[] = '</tbody></table>'.
		'</body></html>';

		$sResult = implode(PHP_EOL,$aResult);

		header('Content-Type: text/html; charset=UTF-8');
		header('Content-Length: '.mb_strlen($sResult));
		header('Content-Disposition: attachment; filename='.$sTitle.'.html');
		echo $sResult;
		die;

	}

	protected function exportToXls1($rs,$fields)
	{
		$sTitle = 'users-'.$this->okt->request->getSchemeAndHttpHost().$this->okt->config->app_path.'-'.date('YmdHis');

		$aResult = array();
		$aResult[] = '<table><tr>';

		$aAllowedFields = self::getAllowedFields();

		foreach ($fields as $field) {
			$aResult[] = '<th>'.html::escapeHTML(mb_convert_encoding($aAllowedFields[$field],'ISO-8859-1','UTF-8')).'</th>';
		}

		$aResult[] = '</tr>';

		while ($rs->fetch())
		{
			$aLine = array();
			foreach ($fields as $field) {
				$aLine[] = html::escapeHTML(mb_convert_encoding($rs->$field,'ISO-8859-1','UTF-8'));
			}
			$aResult[] = '<tr><td>'.implode('</td><td>',$aLine).'</td></tr>';
		}

		$aResult[] = '</table>';

		$sResult = implode(PHP_EOL,$aResult);

		header('Content-Type: application/msexcel; charset=ISO-8859-1');
		header('Content-Length: '.mb_strlen($sResult,'ISO-8859-1'));
		header('Content-Disposition: attachment; filename='.$sTitle.'.xls');
		echo $sResult;
		die;
	}

	protected function exportToXls2($rs,$fields)
	{
		$sTitle = 'users-'.$this->okt->request->getSchemeAndHttpHost().$this->okt->config->app_path.'-'.date('YmdHis');

		$aResult = array();

		$aAllowedFields = self::getAllowedFields();

		$aHeaders = array();
		foreach ($fields as $field) {
			$aHeaders[] = mb_convert_encoding($aAllowedFields[$field],'ISO-8859-1','UTF-8');
		}
		$aResult[] = implode("\t",$aHeaders);

		while ($rs->fetch())
		{
			$aLine = array();
			foreach ($fields as $field) {
				$value = str_replace('"','""',$rs->$field);
				$aLine[] = '"'.html::escapeHTML(mb_convert_encoding($value,'ISO-8859-1','UTF-8')).'"';
			}
			$aResult[] = implode("\t",$aLine);
		}

		$sResult = implode(PHP_EOL,$aResult);

		header('Content-Type: application/msexcel; charset=ISO-8859-1');
		header('Content-Length: '.mb_strlen($sResult,'ISO-8859-1'));
		header('Content-Disposition: attachment; filename='.$sTitle.'.xls');
		echo $sResult;
		die;
	}

	public static function getAllowedFields()
	{
		return array(
			'username' 	=> __('c_c_user_Username'),
			'email' 	=> __('c_c_Email'),
			'title' 	=> __('c_c_Group'),
			'lastname' 	=> __('c_c_Name'),
			'firstname' => __('c_c_First_name')
		);
	}

	public static function getAllowedFormats()
	{
		return array(
			'html' 			=> __('m_users_export_type_html'),
			'csvUtf8' 		=> __('m_users_export_type_csv_utf_8'),
			'csvIso88591' 	=> __('m_users_export_type_csv_iso'),
			'xls1' 			=> __('m_users_export_type_excel_1'),
			'xls2' 			=> __('m_users_export_type_excel_2')
		);
	}


	/* Utils
	----------------------------------------------------------*/

	/**
	 * Retourne l'URL de la page de connexion en fonction de la configuration.
	 *
	 * @param string $sRedirectUrl
	 * @return string
	 * @deprecated
	 */
	public function getLoginUrl($sRedirectUrl=null)
	{
		return UsersHelpers::getLoginUrl($sRedirectUrl);
	}

	/**
	 * Retourne le chemin du template de la page du formulaire de mot de passe oublié.
	 *
	 * @return string
	 */
	public function getForgottenPasswordTplPath()
	{
		return 'users/forgotten_password/'.$this->config->templates['forgotten_password']['default'].'/template';
	}

	/**
	 * Retourne le chemin du template de la page du formulaire d'identification.
	 *
	 * @return string
	 */
	public function getLoginTplPath()
	{
		return 'users/login/'.$this->config->templates['login']['default'].'/template';
	}

	/**
	 * Retourne le chemin du template de la page unifiée des formulaires d'identification et d'inscription.
	 *
	 * @return string
	 */
	public function getLoginRegisterTplPath()
	{
		return 'users/login_register/'.$this->config->templates['login_register']['default'].'/template';
	}

	/**
	 * Retourne le chemin du template de la page du formulaire de profil utilisateur.
	 *
	 * @return string
	 */
	public function getProfileTplPath()
	{
		return 'users/profile/'.$this->config->templates['profile']['default'].'/template';
	}

	/**
	 * Retourne le chemin du template de la page du formulaire d'inscription.
	 *
	 * @return string
	 */
	public function getRegisterTplPath()
	{
		return 'users/register/'.$this->config->templates['register']['default'].'/template';
	}

	/**
	 * Retourne le chemin du template de la page du formulaire d'inscription.
	 *
	 * @return string
	 */
	public function getUserBarTplPath()
	{
		return 'users/user_bar/'.$this->config->templates['user_bar']['default'].'/template';
	}


	/**
	 * Retourne la liste des civilités.
	 *
	 * @param boolean $flip
	 * @return array
	 */
	public static function getCivilities($flip=false)
	{
		$a = array(
			1 => __('c_c_user_civility_1'),
			2 => __('c_c_user_civility_2'),
			3 => __('c_c_user_civility_3')
		);

		if ($flip) {
			$a = array_flip($a);
		}

		return $a;
	}

	/**
	 * Retourne la liste des utilisateurs sous forme de tableau.
	 *
	 * @return array
	 */
	public function getArrayUsers($aParams=array())
	{
		$rsUsers = $this->getUsers($aParams);

		if ($rsUsers->isEmpty()) {
			return array();
		}

		$aUsers = array();

		while ($rsUsers->fetch()) {
			$aUsers[$rsUsers->id] = $rsUsers->getData($rsUsers->index());
		}

		return $aUsers;
	}

	/**
	 * Retourne la liste des groupes sous forme de tableau.
	 *
	 * @return array
	 */
	public function getArrayGroups($aParams=array())
	{
		$rsGroups = $this->getGroups($aParams);

		if ($rsGroups->isEmpty()) {
			return array();
		}

		$aGroups = array();

		while ($rsGroups->fetch()) {
			$aGroups[$rsGroups->group_id] = $rsGroups->getData($rsGroups->index());
		}

		return $aGroups;
	}

}
