<?php
/*
 * This file is part of Okatea. For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
namespace Okatea\Admin\Controller\Users;

use Okatea\Admin\Controller;
use Okatea\Tao\Misc\Utilities;
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
        if ($this->okt->request->request->has('form_sent')) 
        {
            $this->aPageData['config'] = array(
                'users_custom_fields_enabled' => $this->okt->request->request->has('p_users_custom_fields_enabled'),
                'users_pages' => array(
                    'login' => $this->okt->request->request->has('p_enable_login_page'),
                    'register' => $this->okt->request->request->has('p_enable_register_page'),
                    'log_reg' => $this->okt->request->request->has('p_enable_log_reg_page'),
                    'forget_password' => $this->okt->request->request->has('p_enable_forget_password_page'),
                    'profile' => $this->okt->request->request->has('p_enable_profile_page')
                ),
            
                'users_registration' => array(
                    'mail_new_registration' => $this->okt->request->request->has('p_mail_new_registration'),
                    'validate_users_registration' => $this->okt->request->request->has('p_validate_users_registration'),
                    'merge_username_email' => $this->okt->request->request->has('p_merge_username_email'),
                    'auto_log_after_registration' => $this->okt->request->request->has('p_auto_log_after_registration'),
                    'user_choose_group' => $this->okt->request->request->has('p_user_choose_group'),
                    'default_group' => $this->okt->request->request->getInt('p_default_group')
                ),
            
                'users_templates' => array(
                    'forgotten_password' => $this->oTemplatesForgottenPassword->getPostConfig(),
                    'login' => $this->oTemplatesLogin->getPostConfig(),
                    'login_register' => $this->oTemplatesLoginRegister->getPostConfig(),
                    'profile' => $this->oTemplatesProfile->getPostConfig(),
                    'register' => $this->oTemplatesRegister->getPostConfig(),
                    'user_bar' => $this->oTemplatesUserBar->getPostConfig()
                )
            );
                
            # -- CORE TRIGGER : adminUsersConfigProcess
            $this->okt->triggers->callTrigger('adminUsersConfigProcess', $this->aPageData);
            
            if ($this->okt->error->isEmpty()) 
            {
 
                
                try 
                {                    
                    $this->okt->config->write($this->aPageData['config']);
                    
                    $this->okt->page->flash->success(__('c_c_confirm_configuration_updated'));
                    
                    return $this->redirect($this->generateUrl('Users_config'));
                } 
                catch (InvalidArgumentException $e) {
                    $this->okt->error->set(__('c_c_error_writing_configuration'));
                    $this->okt->error->set($e->getMessage());
                }
            }
        }
        
        return $this->display();
    }

    protected function init()
    {
        $this->okt->l10n->loadFile($this->okt->options->get('locales_dir').'/'.$this->okt->user->language.'/admin/users');
        
        $this->aPageData = new \ArrayObject(array());
        $this->aPageData['config'] = array(
            'users_custom_fields_enabled' => $this->okt->config->users_custom_fields_enabled,
            'users_pages' => array(
                'login'             => $this->okt->config->users_pages['login'],
                'register'          => $this->okt->config->users_pages['register'],
                'log_reg'           => $this->okt->config->users_pages['log_reg'],
                'forget_password'   => $this->okt->config->users_pages['forget_password'],
                'profile'           => $this->okt->config->users_pages['profile']
            ),
            'users_registration' => array(
                'mail_new_registration'         => $this->okt->config->users_registration['mail_new_registration'],
                'validate_users_registration'   => $this->okt->config->users_registration['validate_users_registration'],
                'merge_username_email'          => $this->okt->config->users_registration['merge_username_email'],
                'auto_log_after_registration'   => $this->okt->config->users_registration['auto_log_after_registration'],
                'user_choose_group'             => $this->okt->config->users_registration['user_choose_group'],
                'default_group'                 => $this->okt->config->users_registration['default_group']
            )
        );
        
        # Gestionnaires de templates
        $this->oTemplatesForgottenPassword = new TemplatesSet(
            $this->okt, 
            $this->okt->config->users_templates['forgotten_password'], 
            'users/forgotten_password', 
            'forgotten_password', 
            $this->generateUrl('Users_config').'?'
        );
        
        $this->oTemplatesLogin = new TemplatesSet(
            $this->okt, 
            $this->okt->config->users_templates['login'], 
            'users/login', 
            'login', 
            $this->generateUrl('Users_config').'?'
        );
        
        $this->oTemplatesLoginRegister = new TemplatesSet(
            $this->okt, 
            $this->okt->config->users_templates['login_register'], 
            'users/login_register', 
            'login_register', 
            $this->generateUrl('Users_config').'?'
        );
        
        $this->oTemplatesProfile = new TemplatesSet(
            $this->okt, 
            $this->okt->config->users_templates['profile'], 
            'users/profile', 
            'profile', 
            $this->generateUrl('Users_config').'?'
        );
        
        $this->oTemplatesRegister = new TemplatesSet(
            $this->okt, 
            $this->okt->config->users_templates['register'], 
            'users/register', 
            'register', 
            $this->generateUrl('Users_config').'?'
        );
        
        $this->oTemplatesUserBar = new TemplatesSet(
            $this->okt, 
            $this->okt->config->users_templates['user_bar'], 
            'users/user_bar', 
            'user_bar', 
            $this->generateUrl('Users_config').'?'
        );
        
        # -- CORE TRIGGER : adminUsersConfigInit
        $this->okt->triggers->callTrigger('adminUsersConfigInit', $this->aPageData);
    }
    
    protected function display()
    {
        # liste des groupes
        $oUsersGroups = new Groups($this->okt);
        $rsGroups = $oUsersGroups->getGroups();
        
        $aGroups = array();
        while ($rsGroups->fetch())
        {
            if (!in_array($rsGroups->group_id, array(Groups::SUPERADMIN, Groups::ADMIN, Groups::GUEST))) {
                $aGroups[Utilities::escapeHTML($rsGroups->title)] = $rsGroups->group_id;
            }
        }
        
        # Construction des onglets
        $this->aPageData['Tabs'] = new \ArrayObject();
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
                'aGroups' => $aGroups
            ))
        );
        
        $this->aPageData['Tabs'][30] = array(
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
