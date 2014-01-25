<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Builder;

use Okatea\Tao\Modules\Module as BaseModule;

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
		# on ajoutent les items au menu configuration
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->configSubMenu->add(
				__('m_builder_menu'),
				$this->okt->adminRouter->generate('Okatea_builder'),
				$this->okt->request->attributes->get('_route') === 'Okatea_builder',
				141,
				$this->okt->checkPerm('okatea_builder'),
				null
			);
			$this->okt->page->configSubMenu->add(
				__('m_builder_menu_config'),
				$this->okt->adminRouter->generate('Okatea_builder_config'),
				$this->okt->request->attributes->get('_route') === 'Okatea_builder_config',
				142,
				$this->okt->checkPerm('okatea_builder'),
				null
			);
		}
	}
}
