<?php
/**
 * @ingroup okt_module_partners
 * @brief La classe d'installation du module partenaires.
 *
 */

use Tao\Modules\ModuleInstall;

class moduleInstall_partners extends ModuleInstall
{
	public function installTestSet()
	{
		$this->checklist->addItem(
			'regenerate_thumbnails',
			$this->okt->partners->regenMinLogos(),
			'Regeneration of thumbnails',
			'Cannot regenerate thumbnails'
		);
	}

	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'partners',
			'partners_add',
			'partners_remove',
			'partners_display'
		));
	}

} # class
