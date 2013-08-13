<?php
/**
 * @ingroup okt_module_development
 * @brief La classe principale du Module développement.
 *
 */


class module_development extends oktModule
{
	protected function prepend()
	{
		global $oktAutoloadPaths;

		# Chargement des principales locales
		l10n::set(dirname(__FILE__).'/locales/'.$this->okt->user->language.'/main');

		# Autoload
		$oktAutoloadPaths['oktDebugBar'] = dirname(__FILE__).'/inc/class.oktDebugBar.php';
		$oktAutoloadPaths['oktModuleBootstrap'] = dirname(__FILE__).'/inc/class.module.bootstrap.php';
		$oktAutoloadPaths['oktModuleBootstrapAdvanced'] = dirname(__FILE__).'/inc/class.module.bootstrap.advanced.php';
		$oktAutoloadPaths['oktModuleBootstrapSimple'] = dirname(__FILE__).'/inc/class.module.bootstrap.simple.php';
		$oktAutoloadPaths['countingFilesAndLines'] = dirname(__FILE__).'/inc/class.countingFilesAndLines.php';

		# permissions
		$this->okt->addPermGroup('development', __('m_development_perm_group'));
			$this->okt->addPerm('development_debug_bar', __('m_development_perm_debug_bar'), 'development');
			$this->okt->addPerm('development_bootstrap', __('m_development_perm_bootstrap'), 'development');
			$this->okt->addPerm('development_counting', __('m_development_perm_counting'), 'development');

		# Config
		$this->config = $this->okt->newConfig('conf_development');

		# Initialisation debug bar
		$this->debugBar = new oktDebugBar($this->okt,$this->config->debug_bar);
	}

	protected function prepend_admin()
	{
		# On détermine si on est actuellement sur ce module
		$this->onThisModule();

		# Chargement des locales admin
		l10n::set(dirname(__FILE__).'/locales/'.$this->okt->user->language.'/admin');

		# On ajoutent un item au menu
		if (!defined('OKT_DISABLE_MENU'))
		{
			$this->okt->page->developmentSubMenu = new htmlBlockList(null,adminPage::$formatHtmlSubMenu);
			$this->okt->page->mainMenu->add(
				__('m_development_menu_development'),
				null,
				ON_DEVELOPMENT_MODULE,
				10000001,
				true,
				null,
				$this->okt->page->developmentSubMenu,
				$this->url().'/icon.png'
			);
				$this->okt->page->developmentSubMenu->add(
					__('m_development_menu_development'),
					'module.php?m=development&amp;action=index',
					ON_DEVELOPMENT_MODULE && (!$this->okt->page->action || $this->okt->page->action === 'index'),
					1,
					$this->okt->checkPerm('development_debug_bar')
				);
				$this->okt->page->developmentSubMenu->add(
					__('m_development_menu_debugbar'),
					'module.php?m=development&amp;action=debug_bar',
					ON_DEVELOPMENT_MODULE && ($this->okt->page->action === 'debug_bar'),
					2,
					$this->okt->checkPerm('development_debug_bar')
				);
				$this->okt->page->developmentSubMenu->add(
					__('m_development_menu_bootstrap'),
					'module.php?m=development&amp;action=bootstrap',
					ON_DEVELOPMENT_MODULE && ($this->okt->page->action === 'bootstrap'),
					3,
					$this->okt->checkPerm('development_bootstrap')
				);
				$this->okt->page->developmentSubMenu->add(
					__('m_development_menu_counting'),
					'module.php?m=development&amp;action=counting',
					ON_DEVELOPMENT_MODULE && ($this->okt->page->action === 'counting'),
					4,
					$this->okt->checkPerm('development_counting')
				);
		}

		# Message admin home
		if ($this->okt->user->is_superadmin)
		{
			$this->okt->triggers->registerTrigger('adminIndexHtmlContent',
				array('module_development','adminIndexHtmlContent'));
		}

		# Add admin debug bar
		$this->debugBar->loadInAdminPart();
	}

	protected function prepend_public()
	{
		# Add public debug bar
		$this->debugBar->loadInPublicPart();

		# Ajout d'éléments à la barre admin
		if ($this->okt->user->is_superadmin)
		{
			$this->okt->triggers->registerTrigger('publicAdminBarItems',
				array('module_development', 'publicAdminBarItems'));
		}
	}

	/**
	 * Ajout d'un avertissement sur la page d'accueil de l'admin.
	 *
	 * @param oktCore $okt
	 * @return void
	 */
	public static function adminIndexHtmlContent($okt)
	{
		echo
		'<div class="ui-widget" style="width: 700px; margin:0 auto 20px auto;">'.
			'<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 5px 8px;">'.
				'<p class="ui-helper-clearfix "><span class="ui-icon ui-icon-info" style="float: left; margin: 2px 8px 0 0;"></span>'.
				'<span style="float: left;">'.__('m_development_adminIndexHtmlContent').'</span></p>'.
			'</div>'.
		'</div>';
	}

	/**
	 * Ajout d'un avertissement sur la barre admin côté publique.
	 *
	 * @param oktCore $okt
	 * @param arrayObject $aPrimaryAdminBar
	 * @param arrayObject $aSecondaryAdminBar
	 * @param arrayObject $aBasesUrl
	 * @return void
	 */
	public static function publicAdminBarItems($okt, $aPrimaryAdminBar, $aSecondaryAdminBar, $aBasesUrl)
	{
		$aPrimaryAdminBar[10]['items'][100] = array(
			'href' => $aBasesUrl['admin'].'/configuration.php?action=modules',
			'title' => __('m_development_ab_module_enable_title'),
			'intitle' => __('m_development_ab_module_enable')
		);
	}


} # class
