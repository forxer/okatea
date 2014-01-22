<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Admin\Controller\Users;

use Okatea\Admin\Controller;
use Okatea\Tao\Users\Authentification;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Themes\TemplatesSet;

class Config extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('users_config')) {
			return $this->serve401();
		}

		# Gestionnaires de templates
		$oTemplatesForgottenPassword = new TemplatesSet($this->okt,
			$this->okt->config->users_templates['forgotten_password'],
			'users/forgotten_password',
			'forgotten_password',
			$this->generateUrl('Users_config').'?'
		);

		$oTemplatesLogin = new TemplatesSet($this->okt,
			$this->okt->config->users_templates['login'],
			'users/login',
			'login',
			$this->generateUrl('Users_config').'?'
		);

		$oTemplatesLoginRegister = new TemplatesSet($this->okt,
			$this->okt->config->users_templates['login_register'],
			'users/login_register',
			'login_register',
			$this->generateUrl('Users_config').'?'
		);

		$oTemplatesProfile = new TemplatesSet($this->okt,
			$this->okt->config->users_templates['profile'],
			'users/profile',
			'profile',
			$this->generateUrl('Users_config').'?'
		);

		$oTemplatesRegister = new TemplatesSet($this->okt,
			$this->okt->config->users_templates['register'],
			'users/register',
			'register',
			$this->generateUrl('Users_config').'?'
		);

		$oTemplatesUserBar = new TemplatesSet($this->okt,
			$this->okt->config->users_templates['user_bar'],
			'users/user_bar',
			'user_bar',
			$this->generateUrl('Users_config').'?'
		);

		# -- CORE TRIGGER : adminModUsersConfigInit
		$this->okt->triggers->callTrigger('adminModUsersConfigInit');

		# enregistrement configuration
		if ($this->okt->request->request->has('form_sent'))
		{
			if ($this->okt->error->isEmpty())
			{
				$new_conf = array(
					'enable_custom_fields' 			=> $this->okt->request->request->has('p_enable_custom_fields'),
					'users_pages' => array(
						'login' 			=> $this->okt->request->request->has('p_enable_login_page'),
						'register' 			=> $this->okt->request->request->has('p_enable_register_page'),
						'log_reg' 			=> $this->okt->request->request->has('p_enable_log_reg_page'),
						'forget_password' 	=> $this->okt->request->request->has('p_enable_forget_password_page'),
						'profile' 			=> $this->okt->request->request->has('p_enable_profile_page')
					),

					'users_registration' => array(
						'mail_new_registration' 		=> $this->okt->request->request->has('p_mail_new_registration'),
						'validate_users_registration' 	=> $this->okt->request->request->has('p_validate_users_registration'),
						'merge_username_email' 			=> $this->okt->request->request->has('p_merge_username_email'),
						'auto_log_after_registration' 	=> $this->okt->request->request->has('p_auto_log_after_registration'),
						'user_choose_group' 			=> $this->okt->request->request->has('p_user_choose_group'),
						'default_group' 				=> $this->okt->request->request->getInt('p_default_group'),
					),

					'users_templates' => array(
						'forgotten_password' 	=> $oTemplatesForgottenPassword->getPostConfig(),
						'login' 				=> $oTemplatesLogin->getPostConfig(),
						'login_register' 		=> $oTemplatesLoginRegister->getPostConfig(),
						'profile' 				=> $oTemplatesProfile->getPostConfig(),
						'register' 				=> $oTemplatesRegister->getPostConfig(),
						'user_bar' 				=> $oTemplatesUserBar->getPostConfig()
					)
				);

				try
				{
					# -- CORE TRIGGER : adminModUsersConfigProcess
					$this->okt->triggers->callTrigger('adminModUsersConfigProcess');

					$this->okt->Users->config->write($new_conf);

					$this->okt->page->flash->success(__('c_c_confirm_configuration_updated'));

					return $this->redirect($this->generateUrl('Users_config'));
				}
				catch (InvalidArgumentException $e)
				{
					$this->okt->error->set(__('c_c_error_writing_configuration'));
					$this->okt->error->set($e->getMessage());
				}
			}
		}

		# liste des groupes
		$rsGroups = $this->okt->Users->getGroups();

		$aGroups = array();
		while ($rsGroups->fetch())
		{
			if (!in_array($rsGroups->group_id, array(Authentification::superadmin_group_id, Authentification::admin_group_id, Authentification::guest_group_id))) {
				$aGroups[Utilities::escapeHTML($rsGroups->title)] = $rsGroups->group_id;
			}
		}

		# Construction des onglets
		$aConfigTabs = new \ArrayObject;
		$aConfigTabs[10] = array(
			'id' => 'tab_general',
			'title' => __('m_users_General'),
			'content' => $this->renderView('Users/Config/Tabs/General', array(
			))
		);

		$aConfigTabs[20] = array(
			'id' => 'tab_register',
			'title' => __('m_users_Registration'),
			'content' => $this->renderView('Users/Config/Tabs/Registration', array(
				'aGroups' 						=> $aGroups
			))
		);

		$aConfigTabs[30] = array(
			'id' => 'tab_tpl',
			'title' => __('m_users_config_tab_tpl'),
			'content' => $this->renderView('Users/Config/Tabs/Tpl', array(
				'oTemplatesForgottenPassword' 	=> $oTemplatesForgottenPassword,
				'oTemplatesLogin' 				=> $oTemplatesLogin,
				'oTemplatesLoginRegister' 		=> $oTemplatesLoginRegister,
				'oTemplatesProfile' 			=> $oTemplatesProfile,
				'oTemplatesRegister' 			=> $oTemplatesRegister,
				'oTemplatesUserBar' 			=> $oTemplatesUserBar
			))
		);

		# -- CORE TRIGGER : adminModUsersEditDisplayTabs
		$this->okt->triggers->callTrigger('adminModUsersConfigTabs', $aConfigTabs);

		$aConfigTabs->ksort();

		return $this->render('Users/Admin/Templates/Config/Page', array(
			'aConfigTabs' 					=> $aConfigTabs
		));
	}
}
