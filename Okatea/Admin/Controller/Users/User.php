<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Admin\Controller\Users;

use Okatea\Admin\Controller;
use Okatea\Tao\Misc\Mailer;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Users\Users;
use Okatea\Tao\Users\Groups;

class User extends Controller
{
	protected $aPageData;

	protected $iUserId;

	public function profile()
	{
		$this->init();

		$this->aPageData['user'] = array(
			'id'                 => $this->okt->user->id,
			'group_id'           => $this->okt->user->group_id,
			'civility'           => $this->okt->user->civility,
			'status'             => $this->okt->user->status,
			'username'           => $this->okt->user->username,
			'lastname'           => $this->okt->user->lastname,
			'firstname'          => $this->okt->user->firstname,
			'displayname'        => $this->okt->user->displayname,
			'password'           => '',
			'password_confirm'   => '',
			'email'              => $this->okt->user->email,
			'timezone'           => $this->okt->user->timezone,
			'language'           => $this->okt->user->language
		);

		if ($this->request->request->has('form_sent'))
		{
			$this->aPageData['user'] = array(
				'id'                 => $this->okt->user->id,
				'status'             => 1,
				'group_id'           => $this->okt->user->group_id,
				'civility'           => $this->request->request->getInt('civility'),
				'username'           => $this->request->request->get('username'),
				'lastname'           => $this->request->request->get('lastname'),
				'firstname'          => $this->request->request->get('firstname'),
				'displayname'        => $this->request->request->get('displayname'),
				'password'           => '',
				'password_confirm'   => '',
				'email'              => $this->request->request->get('email'),
				'timezone'           => $this->request->request->get('timezone'),
				'language'           => $this->request->request->get('language')
			);

			if ($this->okt->error->isEmpty())
			{
				$oUsers = new Users($this->okt);

				if ($oUsers->updUser($this->aPageData['user']) !== false)
				{
					$this->page->flash->success(__('c_a_users_profil_edited'));

					return $this->redirect($this->generateUrl('User_profile'));
				}
			}
		}

		return $this->render('Users/User/Profile', array(
			'aPageData'      => $this->aPageData,
			'aLanguages'     => $this->getLanguages(),
			'aCivilities'    => $this->getCivilities()
		));
	}

	public function add()
	{
		if (!$this->okt->checkPerm('users')) {
			return $this->serve401();
		}

		$this->init();

		if ($this->request->request->has('form_sent'))
		{
			$this->aPageData['user'] = array(
				'civility'           => $this->request->request->getInt('civility'),
				'status'             => $this->request->request->getInt('status'),
				'username'           => $this->request->request->get('username'),
				'lastname'           => $this->request->request->get('lastname'),
				'firstname'          => $this->request->request->get('firstname'),
				'displayname'        => $this->request->request->get('displayname'),
				'password'           => $this->request->request->get('password'),
				'password_confirm'   => $this->request->request->get('password_confirm'),
				'email'              => $this->request->request->get('email'),
				'timezone'           => $this->request->request->get('timezone'),
				'language'           => $this->request->request->get('language')
			);

			if ($this->okt->error->isEmpty())
			{
				$oUsers = new Users($this->okt);

				if (($iUserId = $oUsers->addUser($this->aPageData['user'])) !== false)
				{
					/*
					if ($this->okt->config->users->custom_fields_enabled)
					{
						while ($rsFields->fetch()) {
							$okt->users->fields->setUserValues($iUserId, $rsFields->id, $aPostedData[$rsFields->id]);
						}
					}
					*/

					$this->page->flash->success(__('c_a_users_user_added'));

					return $this->redirect($this->generateUrl('Users_edit', array('user_id' => $iUserId)));
				}
			}
		}

		return $this->render('Users/User/Add', array(
			'aPageData'      => $this->aPageData,
			'aLanguages'     => $this->getLanguages(),
			'aCivilities'    => $this->getCivilities()
		));
	}

	public function edit()
	{
		if (!$this->okt->checkPerm('users_edit')) {
			return $this->serve401();
		}

		$this->init();

		$this->iUserId = $this->request->attributes->getInt('user_id');

		$oUsers = new Users($this->okt);

		$rsUser = $oUsers->getUser($this->iUserId);

		if (0 === $this->iUserId || 1 === $this->iUserId || $rsUser->isEmpty()) {
			return $this->serve404();
		}

		$this->aPageData['user'] = array(
			'id'                 => $this->iUserId,
			'group_id'           => $rsUser->group_id,
			'civility'           => $rsUser->civility,
			'status'             => $rsUser->status,
			'username'           => $rsUser->username,
			'lastname'           => $rsUser->lastname,
			'firstname'          => $rsUser->firstname,
			'displayname'        => $rsUser->displayname,
			'password'           => '',
			'password_confirm'   => '',
			'email'              => $rsUser->email,
			'timezone'           => $rsUser->timezone,
			'language'           => $rsUser->language
		);

		# un super admin ne peut etre modifié par un non super admin
		if ($this->aPageData['user']['group_id'] == Groups::SUPERADMIN && !$this->okt->user->is_superadmin) {
			return $this->serve401();
		}

		# un admin ne peut etre modifié par un non admin / super admin
		if ($this->aPageData['user']['group_id'] == Groups::ADMIN && !$this->okt->user->is_admin) {
			return $this->serve401();
		}

		if ($this->aPageData['user']['group_id'] == Groups::UNVERIFIED) {
			$this->aPageData['bWaitingValidation'] = true;
		}
		else {
			$this->aPageData['bWaitingValidation'] = false;
		}

		# -- CORE TRIGGER : adminUsersEditInit
		$this->okt->triggers->callTrigger('adminUsersEditInit', $this->aPageData);

		# validate user
		if (false !== ($mUserValidated = $this->validateUser())) {
			return $mUserValidated;
		}

		# change user password
		if (false !== ($mPasswordChanged = $this->updateUserPassword())) {
			return $mPasswordChanged;
		}

		# update user
		if (false !== ($mUserChanged = $this->updateUser())) {
			return $mUserChanged;
		}


		# -- CORE TRIGGER : adminUsersEditProcess
		$this->okt->triggers->callTrigger('adminUsersEditProcess', $this->aPageData);

		$this->aPageData['tabs'] = new \ArrayObject();

		$this->aPageData['tabs'][10] = array(
			'id'         => 'tab-edit-user',
			'title'      => __('c_a_users_General'),
			'content'    => $this->renderView('Users/User/Edit/Tabs/General', array(
				'aPageData'      => $this->aPageData,
				'aLanguages'     => $this->getLanguages(),
				'aCivilities'    => $this->getCivilities(),
				'aGroups'        => $this->getGroups()
			))
		);

		if ($this->okt->checkPerm('change_password'))
		{
			$this->aPageData['tabs'][100] = array(
				'id'        => 'tab-change-password',
				'title'     => __('c_c_user_Password'),
				'content'   => $this->renderView('Users/User/Edit/Tabs/Password', array(
					'aPageData'    => $this->aPageData
				))
			);
		}

		# -- CORE TRIGGER : adminUsersEditBuildTabs
		$this->okt->triggers->callTrigger('adminUsersEditBuildTabs', $this->aPageData);

		$this->aPageData['tabs']->ksort();

		return $this->render('Users/User/Edit/Page', array(
			'aPageData'      => $this->aPageData
		));
	}

	protected function init()
	{
		$this->okt->l10n->loadFile($this->okt->options->get('locales_dir').'/'.$this->okt->user->language.'/admin/users');

		$this->aPageData = new \ArrayObject();

		$this->aPageData['user'] = array(
			'group_id'           => 0,
			'civility'           => 0,
			'status'             => 1,
			'username'           => '',
			'lastname'           => '',
			'firstname'          => '',
			'displayname'        => '',
			'password'           => '',
			'password_confirm'   => '',
			'email'              => '',
			'timezone'           => $this->okt->config->timezone,
			'language'           => $this->okt->config->language
		);
	}

	protected function getLanguages()
	{
		$aLanguages = array();

		foreach ($this->okt->languages->list as $aLanguage) {
			$aLanguages[Utilities::escapeHTML($aLanguage['title'])] = $aLanguage['code'];
		}

		return $aLanguages;
	}

	protected function getCivilities()
	{
		return array_merge(
			array(' ' => 0),
			Users::getCivilities(true)
		);
	}

	protected function getGroups()
	{
		$aGroups = array();

		$oGroups = new Groups($this->okt);
		$rsGroups = $oGroups->getGroups();
		while ($rsGroups->fetch())
		{
			if ($rsGroups->group_id == Groups::SUPERADMIN && !$this->okt->user->is_superadmin) {
				continue;
			}

			$aGroups[Utilities::escapeHTML($rsGroups->title)] = $rsGroups->group_id;
		}

		return $aGroups;
	}

	protected function validateUser()
	{
		if (!$this->request->query->has('validate')) {
			return false;
		}

		$oUsers = new Users($this->okt);
		if ($oUsers->validateUser($this->aPageData['user']['id']))
		{
			$oMail = new Mailer($this->okt);

			$oMail->setFrom();

			$oMail->useFile(__DIR__.'/../../locales/'.$edit_language.'/templates/validate_user.tpl', array(
				'SITE_TITLE'    => Utilities::getSiteTitle($this->aPageData['user']['language']),
				'SITE_URL'      => $this->request->getSchemeAndHttpHost().$okt->config->app_path
			));

			$oMail->message->setTo($this->aPageData['user']['email']);

			$oMail->send();

			$this->page->flash->success(__('m_users_validated_user'));

			return $this->redirect($this->generateUrl('Users_edit', array('user_id' => $this->aPageData['user']['id'])));
		}

		return false;
	}

	protected function updateUserPassword()
	{
		if (!$this->request->request->has('change_password') || !$this->okt->checkPerm('change_password')) {
			return false;
		}

		$aParams = array(
			'id' => $this->aPageData['user']['id']
		);

		$aParams['password'] = $this->request->request->get('password');
		$aParams['password_confirm'] = $this->request->request->get('password_confirm');

		$oUsers = new Users($this->okt);
		if ($oUsers->changeUserPassword($aParams))
		{
			if ($this->request->request->has('send_password_mail'))
			{
				$oMail = new Mailer($this->okt);

				$oMail->setFrom();

				$oMail->useFile(__DIR__.'/../../locales/'.$edit_language.'/templates/admin_change_user_password.tpl', array(
					'SITE_TITLE'      => Utilities::getSiteTitle($edit_language),
					'SITE_URL'        => $this->request->getSchemeAndHttpHost().$this->okt->config->app_path,
					'NEW_PASSWORD'    => $aParams['password']
				));

				$oMail->message->setTo($this->aPageData['user']['email']);
				$oMail->send();
			}

			$this->page->flash->success(__('c_a_users_user_edited'));

			return $this->redirect($this->generateUrl('Users_edit', array('user_id' => $this->aPageData['user']['id'])));
		}

		return false;
	}

	protected function updateUser()
	{
		if (!$this->request->request->has('form_sent')) {
			return false;
		}

		$this->aPageData['user'] = array(
			'id'                 => $this->iUserId,
			'group_id'           => $this->request->request->getInt('group_id'),
			'civility'           => $this->request->request->getInt('civility'),
			'status'             => $this->request->request->getInt('status'),
			'username'           => $this->request->request->get('username'),
			'lastname'           => $this->request->request->get('lastname'),
			'firstname'          => $this->request->request->get('firstname'),
			'displayname'        => $this->request->request->get('displayname'),
			'password'           => $this->request->request->get('password'),
			'password_confirm'   => $this->request->request->get('password_confirm'),
			'email'              => $this->request->request->get('email'),
			'timezone'           => $this->request->request->get('timezone'),
			'language'           => $this->request->request->get('language')
		);

		# peuplement et vérification des champs personnalisés obligatoires
//		if ($this->okt->config->users->custom_fields_enabled) {
//			$okt->users->fields->getPostData($rsFields, $aPostedData);
//		}

		if ($this->okt->error->isEmpty())
		{
			$oUsers = new Users($this->okt);

			if ($oUsers->updUser($this->aPageData['user']) !== false)
			{
				/*
				if ($this->okt->config->users->custom_fields_enabled)
				{
					while ($rsFields->fetch()) {
						$okt->users->fields->setUserValues($this->iUserId, $rsFields->id, $aPostedData[$rsFields->id]);
					}
				}
				*/

				$this->page->flash->success(__('c_a_users_user_edited'));

				return $this->redirect($this->generateUrl('Users_edit', array('user_id' => $this->iUserId)));
			}
		}

		return false;
	}

}
