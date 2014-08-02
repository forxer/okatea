<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Builder;

use Okatea\Admin\Menu as AdminMenu;
use Okatea\Admin\Page;
use Okatea\Tao\Extensions\Modules\Module as BaseModule;

class Module extends BaseModule
{

	protected function prepend()
	{
		# permissions
		$this->okt->addPerm('okatea_builder', __('m_builder_perm'), 'configuration');
		
		# Config
		$this->config = $this->okt->newConfig('conf_builder');
	}

	protected function prepend_admin()
	{
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->builderSubMenu = new AdminMenu(null, Page::$formatHtmlSubMenu);
			
			$this->okt->page->mainMenu->add(__('m_builder_menu'), $this->okt->adminRouter->generate('Builder_index'), $this->okt['request']->attributes->get('_route') === 'Builder_index', 12000000, $this->okt->checkPerm('okatea_builder'), null, $this->okt->page->builderSubMenu, $this->okt['public_url'] . '/modules/' . $this->id() . '/module_icon.png');
			$this->okt->page->builderSubMenu->add(__('m_builder_menu'), $this->okt->adminRouter->generate('Builder_index'), $this->okt['request']->attributes->get('_route') === 'Builder_index', 1);
			$this->okt->page->builderSubMenu->add(__('m_builder_menu_config'), $this->okt->adminRouter->generate('Builder_config'), $this->okt['request']->attributes->get('_route') === 'Builder_config', 10);
		}
	}
}
