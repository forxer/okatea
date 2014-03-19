<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\News;

use Okatea\Admin\Menu as AdminMenu;
use Okatea\Admin\Page;
use Okatea\Tao\Extensions\Modules\Module as BaseModule;

class Module extends BaseModule
{
	public $config = null;

	protected $t_fields;
	protected $t_fields_locales;

	protected function prepend()
	{
		# permissions
		$this->okt->addPermGroup('contact', __('m_contact_perm_group'));
			$this->okt->addPerm('contact_usage', 		__('m_contact_perm_global'), 'contact');
			$this->okt->addPerm('contact_recipients', 	__('m_contact_perm_recipients'), 'contact');
			$this->okt->addPerm('contact_fields', 		__('m_contact_perm_fields'), 'contact');
			$this->okt->addPerm('contact_config', 		__('m_contact_perm_config'), 'contact');

		# tables
		$this->t_fields = $this->db->prefix.'mod_contact_fields';
		$this->t_fields_locales = $this->db->prefix.'mod_contact_fields_locales';

		# config
		$this->config = $this->okt->newConfig('conf_contact');

	}

	protected function prepend_admin()
	{
		# on ajoutent un item au menu admin
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->contactSubMenu = new AdminMenu(null, Page::$formatHtmlSubMenu);
			$this->okt->page->mainMenu->add(
				$this->getName(),
				$this->okt->adminRouter->generate('Contact_index'),
				$this->okt->request->attributes->get('_route') === 'Contact_index',
				2000,
				$this->okt->checkPerm('contact_usage'),
				null,
				$this->okt->page->contactSubMenu,
				$this->okt->options->public_url.'/modules/'.$this->id().'/module_icon.png'
			);
				$this->okt->page->contactSubMenu->add(
					__('m_contact_menu_recipients'),
					$this->okt->adminRouter->generate('Contact_index'),
					$this->okt->request->attributes->get('_route') === 'Contact_index',
					10,
					$this->okt->checkPerm('contact_recipients')
				);
				$this->okt->page->contactSubMenu->add(
					__('m_contact_menu_fields'),
					$this->okt->adminRouter->generate('Contact_fields'),
					in_array($this->okt->request->attributes->get('_route'), array('Contact_fields', 'Contact_field')),
					20,
					$this->okt->checkPerm('contact_fields')
				);
				$this->okt->page->contactSubMenu->add(
					__('m_contact_menu_configuration'),
					$this->okt->adminRouter->generate('Contact_config'),
					$this->okt->request->attributes->get('_route') === 'Contact_config',
					30,
					$this->okt->checkPerm('contact_config')
				);
		}
	}

	protected function prepend_public()
	{
		$this->okt->page->loadCaptcha($this->config->captcha);
	}


}
