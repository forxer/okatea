<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Admin\Controller\Users;

use ArrayObject;
use Okatea\Admin\Controller;
use Okatea\Tao\Html\Escaper;
use Okatea\Tao\Misc\Mailer;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Users\Groups;
use Okatea\Tao\Users\Users;

class User extends Controller
{
	protected $aPageData;

	protected $iUserId;

	public function profile()
	{
		$this->init();
		
		$this->aPageData['user'] = array(
			'id' => $this->okt->user->id,
			'group_id' => $this->okt->user->group_id,
			'civility' => $this->okt->user->civility,
			'status' => $this->okt->user->status,
			'username' => $this->okt->user->username,
			'lastname' => $this->okt->user->lastname,
			'firstname' => $this->okt->user->firstname,
			'displayname' => $this->okt->user->displayname,
			'password' => '',
			'password_confirm' => '',
			'email' => $this->okt->user->email,
			'timezone' => $this->okt->user->timezone,
			'language' => $this->okt->user->language
		);
		
		if ($this->okt['request']->request->has('form_sent'))
		{
			$this->aPageData['user'] = array(
				'id' => $this->okt->user->id,
				'status' => 1,
				'group_id' => $this->okt->user->group_id,
				'civility' => $this->okt['request']->request->getInt('civility'),
				'username' => $this->okt['request']->request->get('username'),
				'lastname' => $this->okt['request']->request->get('lastname'),
				'firstname' => $this->okt['request']->request->get('firstname'),
				'displayname' => $this->okt['request']->request->get('displayname'),
				'password' => '',
				'password_confirm' => '',
				'email' => $this->okt['request']->request->get('email'),
				'timezone' => $this->okt['request']->request->get('timezone'),
				'language' => $this->okt['request']->request->get('language')
			);
			
			if (! $this->okt['flash']->hasError())
			{
				if ($this->okt->getUsers()->updUser($this->aPageData['user']) !== false)
				{
					$this->okt['flash']->success(__('c_a_users_profil_edited'));
					
					return $this->redirect($this->generateUrl('User_profile'));
				}
			}
		}
		
		return $this->render('Users/User/Profile', array(
			'aPageData' => $this->aPageData,
			'aLanguages' => $this->getLanguages(),
			'aCivilities' => $this->getCivilities()
		));
	}

	public function add()
	{
		if (! $this->okt->checkPerm('users'))
		{
			return $this->serve401();
		}
		
		$this->init();
		
		if ($this->okt['request']->request->has('form_sent'))
		{
			$this->aPageData['user'] = array(
				'civility' => $this->okt['request']->request->getInt('civility'),
				'status' => $this->okt['request']->request->getInt('status'),
				'username' => $this->okt['request']->request->get('username'),
				'lastname' => $this->okt['request']->request->get('lastname'),
				'firstname' => $this->okt['request']->request->get('firstname'),
				'displayname' => $this->okt['request']->request->get('displayname'),
				'password' => $this->okt['request']->request->get('password'),
				'password_confirm' => $this->okt['request']->request->get('password_confirm'),
				'email' => $this->okt['request']->request->get('email'),
				'timezone' => $this->okt['request']->request->get('timezone'),
				'language' => $this->okt['request']->request->get('language')
			);
			
			if (! $this->okt['flash']->hasError())
			{
				if (($iUserId = $this->okt->getUsers()->addUser($this->aPageData['user'])) !== false)
				{
					/*
					if ($this->okt['config']->users->custom_fields_enabled)
					{
						while ($rsFields->fetch()) {
							$okt->getUsers()->fields->setUserValues($iUserId, $rsFields->id, $aPostedData[$rsFields->id]);
						}
					}
					*/
					
					$this->okt['flash']->success(__('c_a_users_user_added'));
					
					return $this->redirect($this->generateUrl('Users_edit', array(
						'user_id' => $iUserId
					)));
				}
			}
		}
		
		return $this->render('Users/User/Add', array(
			'aPageData' => $this->aPageData,
			'aLanguages' => $this->getLanguages(),
			'aCivilities' => $this->getCivilities()
		));
	}

	public function edit()
	{
		if (! $this->okt->checkPerm('users_edit'))
		{
			return $this->serve401();
		}
		
		$this->init();
		
		$this->iUserId = $this->okt['request']->attributes->getInt('user_id');
		
		$rsUser = $this->okt->getUsers()->getUser($this->iUserId);
		
		if (0 === $this->iUserId || 1 === $this->iUserId || $rsUser->isEmpty())
		{
			return $this->serve404();
		}
		
		$this->aPageData['user'] = array(
			'id' => $this->iUserId,
			'group_id' => $rsUser->group_id,
			'civility' => $rsUser->civility,
			'status' => $rsUser->status,
			'username' => $rsUser->username,
			'lastname' => $rsUser->lastname,
			'firstname' => $rsUser->firstname,
			'displayname' => $rsUser->displayname,
			'password' => '',
			'password_confirm' => '',
			'email' => $rsUser->email,
			'timezone' => $rsUser->timezone,
			'language' => $rsUser->language
		);
		
		# un super admin ne peut etre modifié par un non super admin
		if ($this->aPageData['user']['group_id'] == Groups::SUPERADMIN && ! $this->okt->user->is_superadmin)
		{
			return $this->serve401();
		}
		
		# un admin ne peut etre modifié par un non admin / super admin
		if ($this->aPageData['user']['group_id'] == Groups::ADMIN && ! $this->okt->user->is_admin)
		{
			return $this->serve401();
		}
		
		if ($this->aPageData['user']['group_id'] == Groups::UNVERIFIED)
		{
			$this->aPageData['bWaitingValidation'] = true;
		}
		else
		{
			$this->aPageData['bWaitingValidation'] = false;
		}
		
		# -- CORE TRIGGER : adminUsersEditInit
		$this->okt['triggers']->callTrigger('adminUsersEditInit', $this->aPageData);
		
		# validate user
		if (false !== ($mUserValidated = $this->validateUser()))
		{
			return $mUserValidated;
		}
		
		# change user password
		if (false !== ($mPasswordChanged = $this->updateUserPassword()))
		{
			return $mPasswordChanged;
		}
		
		# update user
		if (false !== ($mUserChanged = $this->updateUser()))
		{
			return $mUserChanged;
		}
		
		# -- CORE TRIGGER : adminUsersEditProcess
		$this->okt['triggers']->callTrigger('adminUsersEditProcess', $this->aPageData);
		
		$this->aPageData['tabs'] = new ArrayObject();
		
		$this->aPageData['tabs'][10] = array(
			'id' => 'tab-edit-user',
			'title' => __('c_a_users_General'),
			'content' => $this->renderView('Users/User/Edit/Tabs/General', array(
				'aPageData' => $this->aPageData,
				'aLanguages' => $this->getLanguages(),
				'aCivilities' => $this->getCivilities(),
				'aGroups' => $this->getGroups()
			))
		);
		
		if ($this->okt->checkPerm('change_password'))
		{
			$this->aPageData['tabs'][100] = array(
				'id' => 'tab-change-password',
				'title' => __('c_c_user_Password'),
				'content' => $this->renderView('Users/User/Edit/Tabs/Password', array(
					'aPageData' => $this->aPageData
				))
			);
		}
		
		# -- CORE TRIGGER : adminUsersEditBuildTabs
		$this->okt['triggers']->callTrigger('adminUsersEditBuildTabs', $this->aPageData);
		
		$this->aPageData['tabs']->ksort();
		
		return $this->render('Users/User/Edit/Page', array(
			'aPageData' => $this->aPageData
		));
	}

	protected function init()
	{
		$this->okt->l10n->loadFile($this->okt['locales_dir'] . '/%s/admin/users');
		
		$this->aPageData = new ArrayObject();
		
		$this->aPageData['user'] = array(
			'group_id' => 0,
			'civility' => 0,
			'status' => 1,
			'username' => '',
			'lastname' => '',
			'firstname' => '',
			'displayname' => '',
			'password' => '',
			'password_confirm' => '',
			'email' => '',
			'timezone' => $this->okt['config']->timezone,
			'language' => $this->okt['config']->language
		);
	}

	protected function getLanguages()
	{
		$aLanguages = array();
		
		foreach ($this->okt['languages']->list as $aLanguage)
		{
			$aLanguages[Escaper::html($aLanguage['title'])] = $aLanguage['code'];
		}
		
		return $aLanguages;
	}

	protected function getCivilities()
	{
		return array_merge(array(
			' ' => 0
		), Users::getCivilities(true));
	}

	protected function getGroups()
	{
		$aParams = array(
			'language' => $this->okt->user->language
		);
		
		$aParams['group_id_not'][] = Groups::GUEST;
		
		if (! $this->okt->user->is_superadmin)
		{
			$aParams['group_id_not'][] = Groups::SUPERADMIN;
		}
		
		if (! $this->okt->user->is_admin)
		{
			$aParams['group_id_not'][] = Groups::ADMIN;
		}
		
		$rsGroups = $this->okt->getGroups()->getGroups($aParams);
		
		$aGroups = array();
		while ($rsGroups->fetch())
		{
			$aGroups[Escaper::html($rsGroups->title)] = $rsGroups->group_id;
		}
		
		return $aGroups;
	}

	protected function validateUser()
	{
		if (! $this->okt['request']->query->has('validate'))
		{
			return false;
		}
		
		if ($this->okt->getUsers()->validateUser($this->aPageData['user']['id']))
		{
			# Initialisation du mailer et envoi du mail
			$oMail = new Mailer($this->okt);
			
			$oMail->setFrom();
			
			$this->okt->l10n->loadFile($this->okt['locales_dir'] . '/%s/emails', $this->aPageData['user']['language']);
			
			$aMailParams = array(
				'site_title' => $this->okt->page->getSiteTitle($rsUser->language),
				'site_url' => $this->okt['request']->getSchemeAndHttpHost() . $this->okt['config']->app_path,
				'user' => Users::getUserDisplayName($this->aPageData['user']['username'], $this->aPageData['user']['lastname'], $this->aPageData['user']['firstname'], $this->aPageData['user']['displayname'])
			);
			
			$oMail->setSubject(sprintf(__('c_c_emails_registration_validated_on_%s'), $aMailParams['site_title']));
			$oMail->setBody($this->renderView('emails/registrationValidated/text', $aMailParams), 'text/plain');
			
			if ($this->viewExists('emails/registrationValidated/html'))
			{
				$oMail->addPart($this->renderView('emails/registrationValidated/html', $aMailParams), 'text/html');
			}
			
			$oMail->message->setTo($this->aPageData['user']['email']);
			
			$oMail->send();
			
			$this->okt['flash']->success(__('m_users_validated_user'));
			
			return $this->redirect($this->generateUrl('Users_edit', array(
				'user_id' => $this->aPageData['user']['id']
			)));
		}
		
		return false;
	}

	protected function updateUserPassword()
	{
		if (! $this->okt['request']->request->has('change_password') || ! $this->okt->checkPerm('change_password'))
		{
			return false;
		}
		
		$aParams = array(
			'id' => $this->aPageData['user']['id']
		);
		
		$aParams['password'] = $this->okt['request']->request->get('password');
		$aParams['password_confirm'] = $this->okt['request']->request->get('password_confirm');
		
		if ($this->okt->getUsers()->changeUserPassword($aParams))
		{
			if ($this->okt['request']->request->has('send_password_mail'))
			{
				# Initialisation du mailer et envoi du mail
				$oMail = new Mailer($this->okt);
				
				$oMail->setFrom();
				
				$this->okt->l10n->loadFile($this->okt['locales_dir'] . '/%s/emails', $this->aPageData['user']['language']);
				
				$aMailParams = array(
					'site_title' => $this->okt->page->getSiteTitle($rsUser->language),
					'site_url' => $this->okt['request']->getSchemeAndHttpHost() . $this->okt['config']->app_path,
					'user' => Users::getUserDisplayName($this->aPageData['user']['username'], $this->aPageData['user']['lastname'], $this->aPageData['user']['firstname'], $this->aPageData['user']['displayname']),
					'password' => $aParams['password']
				);
				
				$oMail->setSubject(sprintf(__('c_c_emails_update_password_on_%s'), $aMailParams['site_title']));
				$oMail->setBody($this->renderView('emails/adminChangeUserPassword/text', $aMailParams), 'text/plain');
				
				if ($this->viewExists('emails/adminChangeUserPassword/html'))
				{
					$oMail->addPart($this->renderView('emails/adminChangeUserPassword/html', $aMailParams), 'text/html');
				}
				
				$oMail->message->setTo($this->aPageData['user']['email']);
				
				$oMail->send();
			}
			
			$this->okt['flash']->success(__('c_a_users_user_edited'));
			
			return $this->redirect($this->generateUrl('Users_edit', array(
				'user_id' => $this->aPageData['user']['id']
			)));
		}
		
		return false;
	}

	protected function updateUser()
	{
		if (! $this->okt['request']->request->has('form_sent'))
		{
			return false;
		}
		
		$this->aPageData['user'] = array(
			'id' => $this->iUserId,
			'group_id' => $this->okt['request']->request->getInt('group_id'),
			'civility' => $this->okt['request']->request->getInt('civility'),
			'status' => $this->okt['request']->request->getInt('status'),
			'username' => $this->okt['request']->request->get('username'),
			'lastname' => $this->okt['request']->request->get('lastname'),
			'firstname' => $this->okt['request']->request->get('firstname'),
			'displayname' => $this->okt['request']->request->get('displayname'),
			'password' => $this->okt['request']->request->get('password'),
			'password_confirm' => $this->okt['request']->request->get('password_confirm'),
			'email' => $this->okt['request']->request->get('email'),
			'timezone' => $this->okt['request']->request->get('timezone'),
			'language' => $this->okt['request']->request->get('language')
		);
		
		# peuplement et vérification des champs personnalisés obligatoires
		//		if ($this->okt['config']->users->custom_fields_enabled) {
		//			$okt->getUsers()->fields->getPostData($rsFields, $aPostedData);
		//		}
		

		if (! $this->okt['flash']->hasError())
		{
			if ($this->okt->getUsers()->updUser($this->aPageData['user']) !== false)
			{
				/*
				if ($this->okt['config']->users->custom_fields_enabled)
				{
					while ($rsFields->fetch()) {
						$okt->getUsers()->fields->setUserValues($this->iUserId, $rsFields->id, $aPostedData[$rsFields->id]);
					}
				}
				*/
				
				$this->okt['flash']->success(__('c_a_users_user_edited'));
				
				return $this->redirect($this->generateUrl('Users_edit', array(
					'user_id' => $this->iUserId
				)));
			}
		}
		
		return false;
	}
}
