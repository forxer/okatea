<?php
/**
 * @ingroup okt_module_media_manager
 * @brief La classe principale du Module Media manager.
 *
 */


class module_media_manager extends oktModule
{
	protected function prepend()
	{
		global $oktAutoloadPaths;

		# chargement des principales locales
		l10n::set(__DIR__.'/locales/'.$this->okt->user->language.'/main');

		# autoload
		$oktAutoloadPaths['oktMedia'] = __DIR__.'/inc/class.oktMedia.php';

		# permissions
		$this->okt->addPermGroup('media_manager',__('m_media_manager_perm_group'));
			$this->okt->addPerm('media',__('m_media_manager_perm_own'),'media_manager');
			$this->okt->addPerm('media_admin', __('m_media_manager_perm_all'),'media_manager');
//			$this->okt->addPerm('media_config', __('m_media_manager_perm_config'),'media_manager');

		# config
		$this->config = $this->okt->newConfig('conf_media_manager');
	}

	protected function prepend_admin()
	{
		# on détermine si on est actuellement sur ce module
		$this->onThisModule();

		# locales
		l10n::set(__DIR__.'/locales/'.$this->okt->user->language.'/admin');

		# on ajoutent un item au menu admin
		if (!defined('OKT_DISABLE_MENU'))
		{
			$this->okt->page->homeSubMenu->add(
				__('Media manager'),
				'module.php?m=media_manager',
				ON_MEDIA_MANAGER_MODULE && (!$this->okt->page->action || $this->okt->page->action === 'index'),
				30,
				($this->okt->checkPerm('media') || $this->okt->checkPerm('media_admin')),
				null
			);
/*
			$this->okt->page->configSubMenu->add(
				__('Media manager'),
				'module.php?m=media_manager&amp;action=config',
				ON_MEDIA_MANAGER_MODULE && ($this->okt->page->action === 'config'),
				30,
				$this->okt->checkPerm('media_config'),
				null
			);
*/
		}
	}


} # class

