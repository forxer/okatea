<?php
/**
 * @ingroup okt_module_antispam
 * @brief La classe principale du module Antispam.
 *
 */

use Okatea\Tao\Modules\Module;

class module_antispam extends Module
{
	protected function prepend()
	{
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
		# on ajoutent un item au menu admin
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->configSubMenu->add(
				__('Antispam'),
				'module.php?m=antispam',
				$this->bCurrentlyInUse,
				25,
				$this->okt->checkPerm('antispam'),
				null
			);
		}
	}

}
