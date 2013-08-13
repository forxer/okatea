<?php
##header##


class module_##module_id## extends oktModule
{
	public $config;

	protected function prepend()
	{
		global $oktAutoloadPaths;

		# chargement des principales locales
		//l10n::set(dirname(__FILE__).'/locales/'.$this->okt->user->language.'/main');

		# autoload
		$oktAutoloadPaths['##module_camel_case_id##Controller'] = dirname(__FILE__).'/inc/class.##module_id##.controller.php';

		# config
		$this->config = $this->okt->newConfig('conf_##module_id##');

		$this->config->url = $this->okt->page->getBaseUrl().$this->config->public_url[$this->okt->user->language];

		# définition des routes
		if ($this->okt->config->internal_router) {
			$this->addRoutes();
		}
	}

	protected function prepend_admin()
	{
		# on détermine si on est actuellement sur ce module
		$this->onThisModule();

		# chargement des locales admin
		l10n::set(dirname(__FILE__).'/locales/'.$this->okt->user->language.'/admin');

		# on ajoutent un item au menu admin
		if (!defined('OKT_DISABLE_MENU'))
		{
			$this->okt->page->configSubMenu->add(
				$this->getName(),
				'module.php?m=##module_id##&amp;action=config',
				ON_##module_upper_id##_MODULE && ($this->okt->page->action === 'config'),
				22,
				$this->okt->checkPerm('is_superadmin'),
				null
			);
		}
	}

	protected function prepend_public()
	{
	}

	/**
	 * Définition des routes.
	 *
	 * @return void
	 */
	protected function addRoutes()
	{
		$this->okt->router->addRoute('##module_camel_case_id##Page', new oktRoute(
			'^('.html::escapeHTML(implode('|',$this->config->public_url)).')$',
			'##module_camel_case_id##Controller', '##module_camel_case_id##Page'
		));
	}


} # class
