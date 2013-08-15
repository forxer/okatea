<?php
/**
 * @ingroup okt_module_menus
 * @brief
 *
 */


class module_menus extends oktModule
{
	public $config;
	public $triggers;

	protected $t_menus;
	protected $t_menus_items;
	protected $t_menus_items_locales;

	protected function prepend()
	{
		global $oktAutoloadPaths;

		# chargement des principales locales
		l10n::set(__DIR__.'/locales/'.$this->okt->user->language.'/main');

		# permissions
		$this->okt->addPermGroup('menus', __('m_menus_perm_group'));
			$this->okt->addPerm('menus_usage', __('m_menus_perm_global'), 'menus');
			$this->okt->addPerm('menus_config', __('m_menus_perm_config'), 'menus');

		# tables
		$this->t_menus = $this->db->prefix.'mod_menus';
		$this->t_menus_items = $this->db->prefix.'mod_menus_items';
		$this->t_menus_items_locales = $this->db->prefix.'mod_menus_items_locales';

		# déclencheurs
		$this->triggers = new oktTriggers();

		# autoload
		//$oktAutoloadPaths['menusController'] = __DIR__.'/inc/class.menus.controller.php';

		# config
		$this->config = $this->okt->newConfig('conf_menus');
	}

	protected function prepend_admin()
	{
		# on détermine si on est actuellement sur ce module
		$this->onThisModule();

		# chargement des locales admin
		l10n::set(__DIR__.'/locales/'.$this->okt->user->language.'/admin');

		# on ajoutent un item au menu admin
		if (!defined('OKT_DISABLE_MENU'))
		{
			$this->okt->page->homeSubMenu->add(
				$this->getName(),
				'module.php?m=menus&amp;action=index',
				ON_MENUS_MODULE && (!$this->okt->page->action || $this->okt->page->action === 'index'),
				20,
				$this->okt->checkPerm('menus_usage'),
				null
			);

			$this->okt->page->configSubMenu->add(
				__('m_menus_menu_menus_config'),
				'module.php?m=menus&amp;action=config',
				ON_MENUS_MODULE && ($this->okt->page->action === 'config'),
				22,
				$this->okt->checkPerm('menus_config'),
				null
			);
		}
	}


} # class
