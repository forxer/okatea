<?php
/**
 * @ingroup okt_module_antispam
 * @brief La classe principale du module Antispam.
 *
 */

use Tao\Modules\Module;

class module_antispam extends Module
{
	protected function prepend()
	{
		# chargement des principales locales
		l10n::set(__DIR__.'/locales/'.$this->okt->user->language.'/main');

		# autoload
		$this->okt->autoloader->addClassMap(array(
			'oktSpamFilter' => __DIR__.'/inc/class.spamfilter.php',
			'oktSpamFilters' => __DIR__.'/inc/class.spamfilters.php',
			'oktAntispam' => __DIR__.'/inc/lib.antispam.php',

			'oktFilterIP' => __DIR__.'/filters/class.filter.ip.php',
			'oktFilterIpLookup' => __DIR__.'/filters/class.filter.iplookup.php',
			'oktFilterLinksLookup' => __DIR__.'/filters/class.filter.linkslookup.php',
			'oktFilterWords' => __DIR__.'/filters/class.filter.words.php'
		));

		# permissions
		$this->okt->addPerm('antispam',__('m_antispam_perm_global'), 'configuration');

		$this->okt->spamfilters = array('oktFilterIP','oktFilterWords','oktFilterIpLookup','oktFilterLinksLookup');
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
			$this->okt->page->configSubMenu->add(
				__('Antispam'),
				'module.php?m=antispam',
				ON_ANTISPAM_MODULE,
				25,
				$this->okt->checkPerm('antispam'),
				null
			);
		}
	}

}
