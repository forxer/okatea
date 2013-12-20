<?php
/**
 * @ingroup okt_module_media_manager
 * @brief La classe principale du Module Media manager.
 *
 */

use Tao\Modules\Module;

class module_media_manager extends Module
{
	protected function prepend()
	{
		# chargement des principales locales
		$this->okt->l10n->loadFile(__DIR__.'/locales/'.$this->okt->user->language.'/main');

		# autoload
		$this->okt->autoloader->addClassMap(array(
			'oktMedia' => __DIR__.'/inc/class.oktMedia.php'
		));

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
		$this->okt->l10n->loadFile(__DIR__.'/locales/'.$this->okt->user->language.'/admin');

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


}

