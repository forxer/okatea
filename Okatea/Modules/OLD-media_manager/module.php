<?php
/**
 * @ingroup okt_module_media_manager
 * @brief La classe principale du Module Media manager.
 *
 */
use Okatea\Tao\Modules\Module;

class module_media_manager extends Module
{

	protected function prepend()
	{
		# autoload
		$this->okt->autoloader->addClassMap(array(
			'oktMedia' => __DIR__ . '/inc/class.oktMedia.php'
		));
		
		# permissions
		$this->okt['permissions']->addPermGroup('media_manager', __('m_media_manager_perm_group'));
		$this->okt['permissions']->addPerm('media', __('m_media_manager_perm_own'), 'media_manager');
		$this->okt['permissions']->addPerm('media_admin', __('m_media_manager_perm_all'), 'media_manager');
		//			$this->okt['permissions']->addPerm('media_config', __('m_media_manager_perm_config'),'media_manager');
		

		# config
		$this->config = $this->okt->newConfig('conf_media_manager');
	}

	protected function prepend_admin()
	{
		# on ajoutent un item au menu admin
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->homeSubMenu->add(__('Media manager'), 'module.php?m=media_manager', $this->bCurrentlyInUse && (! $this->okt->page->action || $this->okt->page->action === 'index'), 30, ($this->okt['visitor']->checkPerm('media') || $this->okt['visitor']->checkPerm('media_admin')), null);
			/*
			$this->okt->page->configSubMenu->add(
				__('Media manager'),
				'module.php?m=media_manager&amp;action=config',
				$this->bCurrentlyInUse && ($this->okt->page->action === 'config'),
				30,
				$this->okt['visitor']->checkPerm('media_config'),
				null
			);
*/
		}
	}
}

