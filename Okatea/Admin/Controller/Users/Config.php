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
use Okatea\Tao\Themes\TemplatesSet;
use Okatea\Tao\Users\Groups;

class Config extends Controller
{
	protected $aPageData;

	public function page()
	{
		if (! $this->okt->checkPerm('users_config')) {
			return $this->serve401();
		}
		
		$this->init();
		
		# enregistrement configuration
		if ($this->okt['request']->request->has('form_sent'))
		{
			$mail_new_registration = $this->okt['request']->request->has('p_mail_new_registration');
			$mail_new_registration_recipients = array();
			
			if ($mail_new_registration)
			{
				$aMailNewRegistrationRecipients = $this->okt['request']->request->get('p_mail_new_registration_recipients', array());
				
				foreach ($aMailNewRegistrationRecipients as $sUser)
				{
					if (! empty($sUser))
					{
						if (! $this->okt->getUsers()->userExists($sUser)) {
							$this->okt['flash']->error(sprintf(__('c_c_users_error_recipients_%s_not_exists'), Escaper::html($sUser)));
						}
						else {
							$mail_new_registration_recipients[] = $sUser;
						}
					}
				}
				
				if (empty($mail_new_registration_recipients)) {
					$this->okt['flash']->error(__('c_c_users_error_specify_at_least_one_recipients'));
				}
			}
			
			$sGravatarDefaultImage = $this->okt['request']->request->get('p_users_gravatar_default_image');
			if (empty($sGravatarDefaultImage)) {
				$sGravatarDefaultImage = null;
			}
			
			$this->aPageData['config'] = array(
				'users' => array(
					'custom_fields_enabled' => $this->okt['request']->request->has('p_users_custom_fields_enabled'),
					'gravatar' => array(
						'enabled' => $this->okt['request']->request->has('p_users_gravatar_enabled'),
						'default_image' => $sGravatarDefaultImage,
						'rating' => $this->okt['request']->request->get('p_users_gravatar_rating')
					),
					'pages' => array(
						'login' => $this->okt['request']->request->has('p_enable_login_page'),
						'register' => $this->okt['request']->request->has('p_enable_register_page'),
						'log_reg' => $this->okt['request']->request->has('p_enable_log_reg_page'),
						'forget_password' => $this->okt['request']->request->has('p_enable_forget_password_page'),
						'profile' => $this->okt['request']->request->has('p_enable_profile_page')
					),
					'registration' => array(
						'merge_username_email' => $this->okt['request']->request->has('p_merge_username_email'),
						'mail_new_registration' => $mail_new_registration,
						'mail_new_registration_recipients' => $mail_new_registration_recipients,
						'validation_email' => $this->okt['request']->request->has('p_validation_email'),
						'validation_admin' => $this->okt['request']->request->has('p_validation_admin'),
						'auto_log_after_registration' => $this->okt['request']->request->has('p_auto_log_after_registration'),
						'user_choose_group' => $this->okt['request']->request->has('p_user_choose_group'),
						'default_group' => $this->okt['request']->request->getInt('p_default_group')
					),
					'templates' => array(
						'forgotten_password' => $this->oTemplatesForgottenPassword->getPostConfig(),
						'login' => $this->oTemplatesLogin->getPostConfig(),
						'login_register' => $this->oTemplatesLoginRegister->getPostConfig(),
						'profile' => $this->oTemplatesProfile->getPostConfig(),
						'register' => $this->oTemplatesRegister->getPostConfig(),
						'user_bar' => $this->oTemplatesUserBar->getPostConfig()
					)
				)
			);
			
			# -- CORE TRIGGER : adminUsersConfigProcess
			$this->okt->triggers->callTrigger('adminUsersConfigProcess', $this->aPageData);
			
			if (! $this->okt['flash']->hasError())
			{
				$this->okt['config']->write($this->aPageData['config']);
				
				$this->okt['flash']->success(__('c_c_confirm_configuration_updated'));
				
				return $this->redirect($this->generateUrl('Users_config'));
			}
		}
		
		return $this->display();
	}

	protected function init()
	{
		$this->okt->l10n->loadFile($this->okt->options->get('locales_dir') . '/%s/admin/users');
		
		$this->aPageData = new ArrayObject(array());
		$this->aPageData['config'] = array(
			'users' => array(
				'custom_fields_enabled' => $this->okt['config']->users['custom_fields_enabled'],
				'gravatar' => array(
					'enabled' => $this->okt['config']->users['gravatar']['enabled'],
					'default_image' => $this->okt['config']->users['gravatar']['default_image'],
					'rating' => $this->okt['config']->users['gravatar']['rating']
				),
				'pages' => array(
					'login' => $this->okt['config']->users['pages']['login'],
					'register' => $this->okt['config']->users['pages']['register'],
					'log_reg' => $this->okt['config']->users['pages']['log_reg'],
					'forget_password' => $this->okt['config']->users['pages']['forget_password'],
					'profile' => $this->okt['config']->users['pages']['profile']
				),
				'registration' => array(
					'merge_username_email' => $this->okt['config']->users['registration']['merge_username_email'],
					'mail_new_registration' => $this->okt['config']->users['registration']['mail_new_registration'],
					'mail_new_registration_recipients' => $this->okt['config']->users['registration']['mail_new_registration_recipients'],
					'validation_email' => $this->okt['config']->users['registration']['validation_email'],
					'validation_admin' => $this->okt['config']->users['registration']['validation_admin'],
					'auto_log_after_registration' => $this->okt['config']->users['registration']['auto_log_after_registration'],
					'user_choose_group' => $this->okt['config']->users['registration']['user_choose_group'],
					'default_group' => $this->okt['config']->users['registration']['default_group']
				)
			)
		);
		
		# Gestionnaires de templates
		$this->oTemplatesForgottenPassword = new TemplatesSet($this->okt, $this->okt['config']->users['templates']['forgotten_password'], 'users/forgotten_password', 'forgotten_password', $this->generateUrl('Users_config') . '?');
		
		$this->oTemplatesLogin = new TemplatesSet($this->okt, $this->okt['config']->users['templates']['login'], 'users/login', 'login', $this->generateUrl('Users_config') . '?');
		
		$this->oTemplatesLoginRegister = new TemplatesSet($this->okt, $this->okt['config']->users['templates']['login_register'], 'users/login_register', 'login_register', $this->generateUrl('Users_config') . '?');
		
		$this->oTemplatesProfile = new TemplatesSet($this->okt, $this->okt['config']->users['templates']['profile'], 'users/profile', 'profile', $this->generateUrl('Users_config') . '?');
		
		$this->oTemplatesRegister = new TemplatesSet($this->okt, $this->okt['config']->users['templates']['register'], 'users/register', 'register', $this->generateUrl('Users_config') . '?');
		
		$this->oTemplatesUserBar = new TemplatesSet($this->okt, $this->okt['config']->users['templates']['user_bar'], 'users/user_bar', 'user_bar', $this->generateUrl('Users_config') . '?');
		
		# -- CORE TRIGGER : adminUsersConfigInit
		$this->okt->triggers->callTrigger('adminUsersConfigInit', $this->aPageData);
	}

	protected function display()
	{
		# Liste des utilisateurs pour les destinataires de nouvelle inscription
		$rsUsers = $this->okt->getUsers()->getUsers(array(
			'group_id' => array(
				Groups::SUPERADMIN,
				Groups::ADMIN
			)
		));
		
		$aUsers = array();
		while ($rsUsers->fetch())
		{
			$aUsers[Escaper::html($rsUsers->username . (! empty($rsUsers->displayname) ? ' (' . $rsUsers->displayname . ')' : ''))] = Escaper::html($rsUsers->username);
		}
		
		# Liste des groupes par défaut
		$rsGroups = $this->okt->getGroups()->getGroups(array(
			'language' => $this->okt->user->language,
			'group_id_not' => array(
				Groups::SUPERADMIN,
				Groups::ADMIN,
				Groups::GUEST
			)
		));
		
		$aGroups = array();
		while ($rsGroups->fetch())
		{
			$aGroups[Escaper::html($rsGroups->title)] = $rsGroups->group_id;
		}
		
		# Construction des onglets
		$this->aPageData['Tabs'] = new ArrayObject();
		$this->aPageData['Tabs'][10] = array(
			'id' => 'tab_general',
			'title' => __('c_a_users_General'),
			'content' => $this->renderView('Users/Config/Tabs/General', array(
				'aPageData' => $this->aPageData
			))
		);
		
		$this->aPageData['Tabs'][20] = array(
			'id' => 'tab_register',
			'title' => __('c_a_users_Registration'),
			'content' => $this->renderView('Users/Config/Tabs/Registration', array(
				'aPageData' => $this->aPageData,
				'aUsers' => $aUsers,
				'aGroups' => $aGroups
			))
		);
		
		$this->aPageData['Tabs'][30] = array(
			'id' => 'tab_image',
			'title' => __('c_a_users_config_tab_image'),
			'content' => $this->renderView('Users/Config/Tabs/Image', array(
				'aPageData' => $this->aPageData
			))
		);
		
		$this->aPageData['Tabs'][40] = array(
			'id' => 'tab_tpl',
			'title' => __('c_a_users_config_tab_tpl'),
			'content' => $this->renderView('Users/Config/Tabs/Tpl', array(
				'oTemplatesForgottenPassword' => $this->oTemplatesForgottenPassword,
				'oTemplatesLogin' => $this->oTemplatesLogin,
				'oTemplatesLoginRegister' => $this->oTemplatesLoginRegister,
				'oTemplatesProfile' => $this->oTemplatesProfile,
				'oTemplatesRegister' => $this->oTemplatesRegister,
				'oTemplatesUserBar' => $this->oTemplatesUserBar
			))
		);
		
		# -- CORE TRIGGER : adminUsersEditDisplayTabs
		$this->okt->triggers->callTrigger('adminUsersConfigTabs', $this->aPageData['Tabs']);
		
		$this->aPageData['Tabs']->ksort();
		
		return $this->render('Users/Config/Page', array(
			'aPageData' => $this->aPageData
		));
	}
}
